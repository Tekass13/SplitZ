function showMenu() {
    const items = document.getElementsByClassName('item');
    Array.from(items).forEach(element => {
        element.classList.toggle('hidden');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const buttonHidden = document.getElementById('button-hidden');

    if (buttonHidden) {
        // Afficher le menu déroulant
        buttonHidden.addEventListener('click', showMenu);
    }
});