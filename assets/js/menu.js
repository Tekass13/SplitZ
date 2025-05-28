// Sélection des éléments du menu mobile
const menuBtn = document.getElementById('menu-btn');
const mainMenu = document.getElementById('main-menu');
const menuOverlay = document.getElementById('menu-overlay');

// Sélection des éléments du menu profil
const btnProfil = document.getElementById('btn-profil');
const usernameMenu = document.getElementById('username');
const profilMenuItems = usernameMenu.querySelectorAll('.item');

// Fonction pour basculer l'affichage du menu mobile
function toggleMobileMenu() {
    mainMenu.style.display = mainMenu.style.display === 'block' ? 'none' : 'block';
    menuOverlay.style.display = mainMenu.style.display === 'block' ? 'block' : 'none';
}

// Fonction pour gérer l'affichage du menu selon la taille de l'écran
function handleResize() {
    if (window.innerWidth >= 600) {
        mainMenu.style.display = 'flex';
        menuOverlay.style.display = 'none';
    } else if (mainMenu.style.display === 'flex') {
        mainMenu.style.display = 'none';
        menuOverlay.style.display = 'none';
    }
}

// Attente du chargement complet du DOM avant d'ajouter les écouteurs d'événements
document.addEventListener('DOMContentLoaded', () => {
    // Gestion du clic sur le bouton profil
    btnProfil.addEventListener('click', () => {
        profilMenuItems.forEach(item => {
            item.classList.toggle('hidden');
        });
    });
    
    // Fermeture du menu profil lors d'un clic à l'extérieur
    document.addEventListener('click', (event) => {
        const isClickInside = btnProfil.contains(event.target) || usernameMenu.contains(event.target);
        if (!isClickInside) {
            profilMenuItems.forEach(item => {
                item.classList.add('hidden');
            });
        }
    });
    
    // Écouteurs d'événements pour le menu mobile
    menuBtn.addEventListener('click', toggleMobileMenu);
    menuOverlay.addEventListener('click', toggleMobileMenu);
    
    // Gestion du redimensionnement de la fenêtre
    window.addEventListener('resize', handleResize);
    
    // Appel initial pour configurer l'affichage correct
    handleResize();
});