
// Revenir à la page précédente
function previousPage() {
    history.back();
}

// Attente du chargement complet du DOM avant d'ajouter les écouteurs d'événements
document.addEventListener('DOMContentLoaded', () => {
    const backButton = document.getElementById('back-btn');
    if (backButton) {
        backButton.addEventListener('click', previousPage);
    }
});