require('./bootstrap');

// ----------------------    CONSTANTES    ----------------------

// Elementos
const searchInput = document.getElementById('search-input'),
    searchResults = document.getElementById('search-results')

// Funções
const searchVisible = (visible) => {
    const inputActive = ['bg-white', 'text-black', 'placeholder-slate-400'],
        inputInactive = ['bg-white/50', 'text-white', 'placeholder-white'],
        resultsActive = ['hidden']

    if (visible) {
        searchInput.classList.remove(...inputInactive)
        searchInput.classList.add(...inputActive)
        searchResults.classList.remove(...resultsActive)
    } else {
        searchInput.classList.add(...inputInactive)
        searchInput.classList.remove(...inputActive)
        searchResults.classList.add(...resultsActive)
    }
}


// ----------------------    EVENTOS    ----------------------

// Pesquisa
searchInput.addEventListener('focus', () => searchVisible(true))
searchInput.addEventListener('blur', () => searchVisible(false))
