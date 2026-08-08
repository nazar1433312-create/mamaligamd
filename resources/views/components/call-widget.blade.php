<div>
    <audio id="call-remote-audio" autoplay></audio>

    <div id="call-incoming-banner" class="hidden fixed bottom-4 right-4 z-50 bg-white shadow-lg border border-gray-200 rounded-xl p-4 w-72">
        <div class="text-sm text-gray-500 mb-1">📞 {{ __('Входящий звонок') }}</div>
        <div id="call-incoming-name" class="font-semibold mb-3"></div>
        <div class="flex gap-2">
            <button onclick="MamaligaCall.accept()" class="flex-1 bg-green-600 text-white text-sm py-2 rounded-md hover:bg-green-700">✅ {{ __('Ответить') }}</button>
            <button onclick="MamaligaCall.decline()" class="flex-1 bg-red-600 text-white text-sm py-2 rounded-md hover:bg-red-700">❌ {{ __('Отклонить') }}</button>
        </div>
    </div>

    <div id="call-active-bar" class="hidden fixed bottom-4 right-4 z-50 bg-white shadow-lg border border-gray-200 rounded-xl p-4 w-72">
        <div id="call-active-name" class="font-semibold"></div>
        <div id="call-active-status" class="text-sm text-gray-500 mb-3"></div>
        <button onclick="MamaligaCall.hangup()" class="w-full bg-red-600 text-white text-sm py-2 rounded-md hover:bg-red-700">📴 {{ __('Завершить') }}</button>
    </div>
</div>

<script>
(function () {
    if (window.MamaligaCall) {
        window.MamaligaCall.startRingWatcher();
        return;
    }

    const Call = {
        pc: null,
        callId: null,
        role: null,
        localStream: null,
        ringPollTimer: null,
        candidatePollTimer: null,
        statusPollTimer: null,
        lastCandidateId: 0,
        answerSet: false,

        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            @if (config('services.turn.host') && config('services.turn.username'))
            {
                urls: [
                    'turn:{{ config('services.turn.host') }}:{{ config('services.turn.port') }}?transport=udp',
                    'turn:{{ config('services.turn.host') }}:{{ config('services.turn.port') }}?transport=tcp',
                ],
                username: '{{ config('services.turn.username') }}',
                credential: '{{ config('services.turn.password') }}',
            },
            @endif
        ],

        audioCtx: null,
        ringtoneTimer: null,
        ringbackTimer: null,

        getAudioCtx() {
            if (!this.audioCtx) {
                const Ctx = window.AudioContext || window.webkitAudioContext;
                this.audioCtx = new Ctx();
            }
            if (this.audioCtx.state === 'suspended') {
                this.audioCtx.resume().catch(() => {});
            }
            return this.audioCtx;
        },

        beep(freqs, duration, volume) {
            try {
                const ctx = this.getAudioCtx();
                const gain = ctx.createGain();
                gain.gain.value = volume;
                gain.connect(ctx.destination);
                freqs.forEach((f) => {
                    const osc = ctx.createOscillator();
                    osc.type = 'sine';
                    osc.frequency.value = f;
                    osc.connect(gain);
                    osc.start();
                    osc.stop(ctx.currentTime + duration);
                });
            } catch (e) {}
        },

        startRingtone() {
            if (this.ringtoneTimer) return;
            const ring = () => this.beep([440, 480], 0.9, 0.12);
            ring();
            this.ringtoneTimer = setInterval(ring, 1800);
        },

        stopRingtone() {
            clearInterval(this.ringtoneTimer);
            this.ringtoneTimer = null;
        },

        startRingback() {
            if (this.ringbackTimer) return;
            const beep = () => this.beep([425], 1, 0.08);
            beep();
            this.ringbackTimer = setInterval(beep, 3000);
        },

        stopRingback() {
            clearInterval(this.ringbackTimer);
            this.ringbackTimer = null;
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]').content;
        },

        async post(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                body: JSON.stringify(body || {}),
            });
            return res.json();
        },

        async get(url) {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            return res.json();
        },

        startRingWatcher() {
            if (this.ringPollTimer) return;
            this.ringPollTimer = setInterval(async () => {
                if (this.callId) return;
                try {
                    const data = await this.get('/calls/incoming');
                    if (data.call) this.showIncoming(data.call);
                } catch (e) {}
            }, 3000);
        },

        showIncoming(call) {
            this.callId = call.id;
            this.role = 'callee';
            document.getElementById('call-incoming-name').textContent = call.caller_name;
            document.getElementById('call-incoming-banner').classList.remove('hidden');
            this.startRingtone();
        },

        async accept() {
            this.stopRingtone();
            document.getElementById('call-incoming-banner').classList.add('hidden');
            const name = document.getElementById('call-incoming-name').textContent;
            try {
                await this.setupPeerConnection();
                const data = await this.get(`/calls/${this.callId}`);
                await this.pc.setRemoteDescription({ type: 'offer', sdp: data.offer_sdp });
                const answer = await this.pc.createAnswer();
                await this.pc.setLocalDescription(answer);
                await this.post(`/calls/${this.callId}/answer`, { sdp: answer.sdp });
                this.showActiveBar(name, '{{ __('В разговоре') }}');
                this.startCandidatePolling();
                this.startStatusPolling();
            } catch (e) {
                this.hangup(true);
            }
        },

        async decline() {
            this.stopRingtone();
            if (this.callId) await this.post(`/calls/${this.callId}/decline`);
            document.getElementById('call-incoming-banner').classList.add('hidden');
            this.reset();
        },

        async start(calleeId, calleeName) {
            if (this.callId) { alert('{{ __('Вы уже в звонке') }}'); return; }
            this.role = 'caller';
            try {
                const created = await this.post('/calls', { callee_id: calleeId });
                if (!created.id) throw new Error('no id');
                this.callId = created.id;
                await this.setupPeerConnection();
                const offer = await this.pc.createOffer();
                await this.pc.setLocalDescription(offer);
                await this.post(`/calls/${this.callId}/offer`, { sdp: offer.sdp });
                this.showActiveBar(calleeName, '{{ __('Вызов...') }}');
                this.startRingback();
                this.startCandidatePolling();
                this.startStatusPolling();
            } catch (e) {
                this.hangup(true);
            }
        },

        async setupPeerConnection() {
            this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.pc = new RTCPeerConnection({ iceServers: this.iceServers });
            this.localStream.getTracks().forEach(t => this.pc.addTrack(t, this.localStream));
            this.pc.ontrack = (e) => {
                const audioEl = document.getElementById('call-remote-audio');
                audioEl.srcObject = e.streams[0];
                audioEl.play().catch(() => {});
            };
            this.pc.onicecandidate = (e) => {
                if (e.candidate && this.callId) {
                    this.post(`/calls/${this.callId}/candidate`, { candidate: JSON.stringify(e.candidate) });
                }
            };
            this.pc.onconnectionstatechange = () => {
                if (this.pc && ['disconnected', 'failed', 'closed'].includes(this.pc.connectionState)) {
                    this.hangup(false);
                }
            };
        },

        startCandidatePolling() {
            this.lastCandidateId = 0;
            this.candidatePollTimer = setInterval(async () => {
                if (!this.callId || !this.pc) return;
                try {
                    const data = await this.get(`/calls/${this.callId}/candidates?since=${this.lastCandidateId}`);
                    for (const sig of data.candidates) {
                        this.lastCandidateId = sig.id;
                        try { await this.pc.addIceCandidate(JSON.parse(sig.candidate)); } catch (e) {}
                    }
                } catch (e) {}
            }, 1500);
        },

        startStatusPolling() {
            this.statusPollTimer = setInterval(async () => {
                if (!this.callId) return;
                try {
                    const data = await this.get(`/calls/${this.callId}`);
                    if (this.role === 'caller' && data.status === 'accepted' && data.answer_sdp && !this.answerSet) {
                        this.answerSet = true;
                        this.stopRingback();
                        await this.pc.setRemoteDescription({ type: 'answer', sdp: data.answer_sdp });
                        this.setActiveStatus('{{ __('В разговоре') }}');
                    }
                    if (['declined', 'ended', 'missed'].includes(data.status)) {
                        this.hangup(false);
                    }
                } catch (e) {}
            }, 1500);
        },

        showActiveBar(name, status) {
            document.getElementById('call-active-name').textContent = name;
            document.getElementById('call-active-status').textContent = status;
            document.getElementById('call-active-bar').classList.remove('hidden');
        },

        setActiveStatus(text) {
            document.getElementById('call-active-status').textContent = text;
        },

        async hangup(notify) {
            this.stopRingtone();
            this.stopRingback();
            if (notify && this.callId) {
                try { await this.post(`/calls/${this.callId}/end`); } catch (e) {}
            }
            if (this.pc) { try { this.pc.close(); } catch (e) {} this.pc = null; }
            if (this.localStream) { this.localStream.getTracks().forEach(t => t.stop()); this.localStream = null; }
            this.reset();
        },

        reset() {
            this.stopRingtone();
            this.stopRingback();
            clearInterval(this.candidatePollTimer);
            clearInterval(this.statusPollTimer);
            this.candidatePollTimer = null;
            this.statusPollTimer = null;
            this.callId = null;
            this.role = null;
            this.answerSet = false;
            document.getElementById('call-active-bar').classList.add('hidden');
            document.getElementById('call-incoming-banner').classList.add('hidden');
        },
    };

    window.MamaligaCall = Call;
    Call.startRingWatcher();

    // Browsers block audio until the page has seen a real user gesture —
    // warm up the AudioContext on the first click/tap so the ringtone can
    // actually play once an incoming call arrives via background polling.
    const unlockAudio = () => {
        Call.getAudioCtx();
        document.removeEventListener('click', unlockAudio);
        document.removeEventListener('touchstart', unlockAudio);
    };
    document.addEventListener('click', unlockAudio);
    document.addEventListener('touchstart', unlockAudio);

    window.addEventListener('beforeunload', () => {
        if (Call.callId) {
            navigator.sendBeacon(`/calls/${Call.callId}/end`);
        }
    });
})();
</script>
