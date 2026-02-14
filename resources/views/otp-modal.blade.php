<style>
    #otp-modal { display: none; position: fixed; inset: 0; z-index: 50; align-items: center; justify-content: center; background: rgba(0,0,0,0.5); font-family: sans-serif; }
    #otp-modal.active { display: flex; }
    .otp-modal-container { background: white; border-radius: 0.5rem; width: 24rem; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
    .otp-modal-title { font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; }
    .otp-modal-text { font-size: 0.875rem; color: #4b5563; margin-bottom: 1rem; }
    .otp-modal-input { width: 100%; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem 0.75rem; margin-top: 0.25rem; box-sizing: border-box; }
    .otp-modal-btn { padding: 0.5rem 1rem; border-radius: 0.25rem; font-weight: 600; cursor: pointer; border: none; }
    .otp-modal-btn-cancel { background: #e5e7eb; color: #374151; }
    .otp-modal-btn-verify { background: #4f46e5; color: white; }
</style>

<div id="otp-modal">
    <div class="otp-modal-container">
        <h2 class="otp-modal-title">Verify OTP</h2>
        <p class="otp-modal-text">Please enter the OTP sent to your email/phone.</p>

        <div style="margin-bottom: 1rem;">
            <label for="otp-input" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">One Time Password</label>
            <input type="text" id="otp-input" maxlength="6" class="otp-modal-input" placeholder="Enter 6-digit OTP">
            <p id="otp-error" style="display: none; color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;"></p>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button onclick="closeOtpModal()" class="otp-modal-btn otp-modal-btn-cancel">Cancel</button>
            <button onclick="verifyOtp()" class="otp-modal-btn otp-modal-btn-verify">Verify</button>
        </div>
    </div>
</div>

<script>
    function openOtpModal() {
        document.getElementById('otp-modal').classList.add('active');
    }

    function closeOtpModal() {
        document.getElementById('otp-modal').classList.remove('active');
        document.getElementById('otp-error').style.display = 'none';
        document.getElementById('otp-input').value = '';
    }

    async function verifyOtp() {
        const otp = document.getElementById('otp-input').value;
        const identifier = document.getElementById('email')?.value || document.getElementById('phone')?.value || ''; // Adjust selector based on your login form

        if (!otp || otp.length < 6) {
            showError('Please enter a valid 6-digit OTP.');
            return;
        }

        try {
            const response = await fetch('/otp/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ identifier, otp })
            });

            const data = await response.json();

            if (response.ok) {
                window.location.reload(); // Or redirect to dashboard
            } else {
                showError(data.message || 'Verification failed.');
            }
        } catch (error) {
            showError('An error occurred. Please try again.');
        }
    }

    function showError(message) {
        const errorEl = document.getElementById('otp-error');
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }
</script>
