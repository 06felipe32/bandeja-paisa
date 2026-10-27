document.querySelectorAll('[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
document.querySelectorAll('[data-password]').forEach(button => {
    button.addEventListener('click', () => {
        const field = document.getElementById(button.dataset.password);
        const visible = field.type === 'password';
        field.type = visible ? 'text' : 'password';
        button.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        button.setAttribute('aria-pressed', String(visible));
    });
});
const roleSelect = document.querySelector('[data-role-select]');
if (roleSelect) {
    const update = () => {
        const container = document.querySelector('[data-client-association]');
        const isClient = roleSelect.value === 'cliente';
        container.hidden = !isClient;
        container.querySelector('select').disabled = !isClient;
    };
    roleSelect.addEventListener('change', update);
    update();
}
