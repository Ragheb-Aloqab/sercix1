{{-- Push this in @push('styles') for pages using the glass layout --}}
<style>
    .company-glass {
        position: relative;
        background-image: url('https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1920');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    .company-glass::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 41, 59, 0.88) 100%);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .company-glass .company-glass-content {
        position: relative;
        z-index: 1;
    }
</style>
