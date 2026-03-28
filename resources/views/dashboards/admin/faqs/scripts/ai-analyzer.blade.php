// 🧠 AI Modal & Ticket Analysis Logic
const aiAnalyzer = {
    openModal() {
        document.getElementById('analyze-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        const btn = document.getElementById('analyze-btn');
        if (Config.unprocessedCount === 0) {
            btn.disabled = true;
            document.getElementById('analyzeText').textContent = 'No Tickets to Analyze';
        }
    },

    closeModal() {
        document.getElementById('analyze-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    },

    async start() {
        const btn = document.getElementById('analyze-btn');
        btn.disabled = true;
        document.getElementById('analyzeIcon').classList.add('hidden');
        document.getElementById('analyzeSpinner').classList.remove('hidden');
        document.getElementById('analyzeText').textContent = 'Analyzing...';
        document.getElementById('progress-section').classList.remove('hidden');

        try {
            const data = await apiFetch(Config.processAnalysisUrl, 'POST');
            if (!data.success) throw new Error(data.message || 'Analysis failed');
            
            this.complete(data);
        } catch (err) {
            console.error('[AiAnalyzer Error]:', err);
            // Uses shared Utils notify
            Utils.notify('error-notification', err.message); 
            this.resetUi();
        }
    },

    complete(data) {
        document.getElementById('tickets-processed').textContent = data.tickets_processed || 0;
        document.getElementById('faqs-generated').textContent = data.faqs_generated || 0;
        
        Utils.notify('success-notification', 'Analysis completed successfully!');
        setTimeout(() => location.reload(), 3000);
    },

    resetUi() {
        const btn = document.getElementById('analyze-btn');
        btn.disabled = false;
        document.getElementById('analyzeIcon').classList.remove('hidden');
        document.getElementById('analyzeSpinner').classList.add('hidden');
        document.getElementById('analyzeText').textContent = 'Start Analysis';
        document.getElementById('progress-section').classList.add('hidden');
    }
};

// Global hooks for standard HTML onclick bindings
window.openAnalyzeModal = () => aiAnalyzer.openModal();
window.closeAnalyzeModal = () => aiAnalyzer.closeModal();
window.startAnalysis = () => aiAnalyzer.start();