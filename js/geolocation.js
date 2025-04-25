// Função para obter a localização do usuário
function getUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Sucesso ao obter localização
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                
                // Salvar a localização no banco de dados via AJAX
                saveUserLocation(latitude, longitude);
                
                // Atualizar elementos da interface, se necessário
                document.getElementById('user-latitude').value = latitude;
                document.getElementById('user-longitude').value = longitude;
            },
            function(error) {
                // Erro ao obter localização
                let errorMessage = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = "Usuário negou a solicitação de geolocalização.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "Informações de localização indisponíveis.";
                        break;
                    case error.TIMEOUT:
                        errorMessage = "Tempo esgotado ao obter localização.";
                        break;
                    case error.UNKNOWN_ERROR:
                        errorMessage = "Erro desconhecido ao obter localização.";
                        break;
                }
                console.error(errorMessage);
                alert("Não foi possível obter sua localização: " + errorMessage);
            }
        );
    } else {
        alert("Geolocalização não é suportada por este navegador.");
    }
}

// Função para salvar a localização do usuário no banco de dados
function saveUserLocation(latitude, longitude) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../includes/save_location.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status === 200) {
            console.log('Localização salva com sucesso!');
        } else {
            console.error('Erro ao salvar localização:', this.responseText);
        }
    };
    xhr.send(`latitude=${latitude}&longitude=${longitude}`);
}

// Inicializar a obtenção da localização quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos em uma página que precisa da localização
    if (document.getElementById('user-latitude') || 
        document.querySelector('.needs-location')) {
        getUserLocation();
    }
    
    // Adicionar evento ao botão de atualizar localização, se existir
    const updateLocationBtn = document.getElementById('update-location-btn');
    if (updateLocationBtn) {
        updateLocationBtn.addEventListener('click', getUserLocation);
    }
});