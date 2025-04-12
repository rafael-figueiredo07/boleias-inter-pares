document.addEventListener("DOMContentLoaded", function() {
    let agora = new Date();
    let horas = agora.getHours();
    let minutos = agora.getMinutes();
    
    // Arredonda os minutos para o próximo múltiplo de 5 (ex: 11:26 → 11:30)
    minutos = Math.ceil(minutos / 5) * 5;
    if (minutos === 60) {
        minutos = 0;
        horas = (horas + 1) % 24;
    }
    
    let horarioAtual = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}`;
    let hoje = agora.toISOString().split('T')[0];
    let inputData = document.getElementById("data");
    let inputHorario = document.getElementById("horario");

    // Configuração inicial
    inputData.min = hoje;
    inputData.value = hoje;
    inputHorario.value = horarioAtual;
    inputHorario.step = 300; // Intervalo fixo de 5 minutos (300 segundos)

    // Atualiza a restrição de horário mínimo quando a data muda
    inputData.addEventListener("change", function() {
        if (inputData.value === hoje) {
            inputHorario.min = horarioAtual;
        } else {
            inputHorario.min = "00:00";
        }
    });

    // Força o valor digitado a ser um múltiplo de 5 minutos (arredondando para cima)
    inputHorario.addEventListener("change", function() {
        let [horas, minutos] = inputHorario.value.split(':').map(Number);
        
        minutos = Math.ceil(minutos / 5) * 5; // Arredonda para cima
        if (minutos >= 60) {
            minutos = 0;
            horas = (horas + 1) % 24;
        }
        
        inputHorario.value = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}`;
    });
});