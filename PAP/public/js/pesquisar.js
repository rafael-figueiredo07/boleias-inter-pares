function invertInputs() {
    let partida = document.getElementById("partida");
    let destino = document.getElementById("destino");

    [partida.value, destino.value] = [destino.value, partida.value];
}

// Obtém a data atual no formato YYYY-MM-DD
let hoje = new Date().toISOString().split('T')[0];

// Define o valor e o mínimo da data no input
let inputData = document.getElementById("data");
inputData.value = hoje;
inputData.min = hoje;

// Atualiza o texto do label com a data atual
document.getElementById("data").value = hoje;
document.getElementById("labelData").innerText = hoje.split('-').reverse().join('/');