const modeInput = document.getElementById('mode');
const toggleBtn = document.getElementById('toggle-mode');
const formTitle = document.getElementById('form-title');
const authForm = document.getElementById('auth-form');
const submitBtn = document.getElementById('submit-btn');

const rowName = document.getElementById('row-name');
const rowCompany = document.getElementById('row-company');
const rowWebsite = document.getElementById('row-website');

function setMode(mode) {
    const isRegister = mode === 'register';
    modeInput.value = mode;

    // Title + button text
    formTitle.textContent = isRegister ? 'Create account' : 'Sign in';
    toggleBtn.textContent = isRegister ? 'Have an account? Sign in' : 'New here? Register';

    // Form action
    authForm.action = isRegister ? '/register' : '/login';

    // Submit button
    submitBtn.textContent = isRegister ? 'Create account' : 'Sign in';

    // Toggle extra fields for registration
    rowName.classList.toggle('hidden', !isRegister);
    rowCompany.classList.toggle('hidden', !isRegister);
    rowWebsite.classList.toggle('hidden', !isRegister);
}

toggleBtn.addEventListener('click', () => {
    setMode(modeInput.value === 'login' ? 'register' : 'login');
});

// Default to login mode
setMode('login');