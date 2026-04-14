<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Nota Fiscal - Sistema</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script> 

    <style>
        /* CSS de layout mantido */
        .form-and-illustration-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px; 
            margin-top: 20px; 
        }
        .form-column {
            flex: 2; 
            min-width: 400px; 
        }
        .illustration-column {
            flex: 1; 
            min-width: 300px;
        }
        @media (max-width: 900px) {
            .form-and-illustration-wrapper {
                flex-direction: column;
                align-items: stretch;
                gap: 20px;
            }
            .form-column, 
            .illustration-column {
                flex: auto;
                width: 100%;
                min-width: auto;
            }
        }
        /* Estilo customizado do botão */
        .btn-primary {
            background-color: #000000ff; /* Cor do seu tema */
            border-color: #d9ae1eff;
            color: white;
            padding: 10px 15px;
            border-radius: 7px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #c9a700ff;
        }
        
        /* Adicionado: Remove as cores de validação do Bootstrap */
        .form-control:focus:not(.is-invalid):not(.is-valid), 
        .form-control:not(:focus):not(.is-invalid):not(.is-valid) {
            border-color: var(--bs-border-color);
            box-shadow: none;
        }
        .form-control.is-valid, .was-validated .form-control:valid,
        .form-control.is-invalid, .was-validated .form-control:invalid {
            border-color: var(--bs-border-color); 
            padding-right: 0.75rem; 
            background-image: none; 
        }
        .valid-feedback, .invalid-feedback {
            display: none !important;
        }

        /* ********************************************************** */
        /* CSS para Impressão (Oculta elementos de navegação na impressão) */
        /* ********************************************************** */
        @media print {
            body > * {
                display: none !important; /* Esconde todos os elementos do corpo por padrão */
            }
            #invoiceContent {
                display: block !important; /* Exibe apenas o conteúdo da Nota Fiscal */
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 15px;
                box-shadow: none;
                border: none;
            }
            /* Garantir que o conteúdo interno da NF também seja visível */
            #invoiceContent * {
                visibility: visible;
            }
            /* Remover cores de fundo do Bootstrap para impressão */
            .table-bordered { 
                border: 1px solid #000 !important; 
            }
            .table-bordered td, .table-bordered th {
                border-color: #000 !important;
            }
            .invoice-content h4, .invoice-content h5 {
                color: #000 !important;
            }
            .invoice-content {
                 font-size: 10pt; 
            }
        }
        
    </style>
</head>
<body class="layout">

    <div id="overlay"></div>

    <a id="btn-toggle" class="sidebar-toggler" href="#">
        <i class="fas fa-bars"></i>
    </a>

    <div id="sidebar" class="sidebar">
        <div class="sidebar-layout">
            
            <div class="sidebar-header">
                <a href="#" class="pro-sidebar-logo">
                    <img 
                        src="logo.png" 
                        alt="Logo Painel de Gestão" 
                        style="width: 60px; height: 60px; margin-right: 2px;"
                    >
                    <h5>2° Logística</h5>
                </a>
            </div>

            <nav class="menu">
                <div class="menu open-current-submenu">
                    <ul>
                        <li class="menu-header">GERENCIAMENTO</li>
                        <li class="menu-item active">
                            <a href="index.html">
                                <span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="menu-title">Início</span>
                            </a>
                        </li>
                        <li class="menu-item"> 
                            <a href="cracha.html">
                                <span class="menu-icon"><i class="fas fa-box-open"></i></span>
                                <span class="menu-title">Crachá</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="edit-order.php">
                                <span class="menu-icon"><i class="fas fa-users"></i></span>
                                <span class="menu-title">Nota Fiscal</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="view-orders.html">
                                <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                                <span class="menu-title">Estatísticas</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="etiqueta.html">
                                <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                                <span class="menu-title">Etiquetas</span>
                            </a>
                        <li class="menu-item">
                            <a href="sorteio.html">
                                <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                                <span class="menu-title">Roleta de Prémios</span>
                            </a>    
                        </li>
                    </ul>
                    <div id="btn-collapse" class="sidebar-collapser"> 
                        <i class="fas fa-angle-double-left" id="collapse-icon"></i> 
                    </div>
                </div>
            </nav>
            </div>
            
    </div>
        
    <div class="content">
        
        <header>
            <div class="logo">Cross Docking</div>
        </header>

        <div class="main-section" style="padding-bottom: 30px; border-bottom: none;">
            <div class="main-text">
                <h1>Gerador de Nota Fiscal Eletrônica</h1>
                <p>Preencha os campos abaixo para **gerar, visualizar e registrar** sua fatura no banco de dados.</p>
            </div>
        </div>

        <div class="second-section">
            <div class="form-and-illustration-wrapper">
                
                <div class="form-column">
                    <div class="card">
                        <div class="card-body p-4"> 
                            <div class="label">DADOS DA NOTA FISCAL</div>
                            <h3 class="mb-4">Informações da Transação</h3> 
                            
                            <form id="invoiceForm"> 
                                
                                <h5 class="mt-3 mb-3 text-secondary">Dados do Cliente (Destinatário)</h5>
                                
                                <div class="mb-3">
                                    <label for="clientName" class="form-label">Nome do Cliente</label>
                                    <input type="text" class="form-control" id="clientName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="clientEmail" class="form-label">E-mail do Cliente</label>
                                    <input type="email" class="form-control" id="clientEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="invoiceTotal" class="form-label">Valor Total (R$)</label>
                                    <input type="number" step="0.01" class="form-control" id="invoiceTotal" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="clientCPF" class="form-label">CPF</label>
                                    <input type="text" class="form-control" id="clientCPF">
                                </div>
                                <div class="mb-3">
                                    <label for="clientPhone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" id="clientPhone">
                                </div>
                                <div class="mb-3">
                                    <label for="transportMethod" class="form-label">Meio de Transporte</label>
                                    <input type="text" class="form-control" id="transportMethod" value="Digital">
                                </div>
                                <div class="mb-3">
                                    <label for="shippingDate" class="form-label">Data de Envio</label>
                                    <input type="date" class="form-control" id="shippingDate" value="2025-11-10">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="invoiceDescription" class="form-label">Descrição do Serviço</label>
                                    <textarea class="form-control" id="invoiceDescription" rows="3"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary" id="generateBtn">
                                    <h5>Gerar Nota Fiscal</h5>
                                </button>
                            </form>
                            
                        </div>
                    </div>
                </div>
                
                <div class="illustration-column">
                    <div class="card p-4 h-100" id="invoicePreview">
                        <div class="text-center text-muted" id="initialPreviewText">
                            <i class="fas fa-file-invoice" style="font-size: 50px; margin-bottom: 15px;"></i>
                            <h5>Pré-Visualização da Nota Fiscal</h5>
                            <p>Preencha os dados ao lado e clique em **"Gerar e Registrar Nota Fiscal"** para ver a pré-visualização e o QR Code simulado aqui.</p>
                        </div>
                        
                        <div id="generatedContent"></div> 
                        
                        <div id="printButtonWrapper" style="margin-top: 15px; text-align: center; display: none;">
                            <button onclick="printInvoice()" class="btn btn-success">
                                <i class="fas fa-print"></i> Imprimir Nota Fiscal
                            </button>
                        </div>
                        </div>
                </div>
                
            </div>
        </div>

        
    </div>
    
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script>
    // Função para gerar a nota fiscal na pré-visualização
    function generateInvoice(event) {
        // Verifica a validação do formulário (Bootstrap)
        const form = document.getElementById('invoiceForm');
        if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        if (event) event.preventDefault(); // Evita o envio do formulário padrão

        const clientName = document.getElementById('clientName').value;
        const clientEmail = document.getElementById('clientEmail').value;
        // Garante que o valor total seja um número com 2 casas decimais
        const invoiceTotal = parseFloat(document.getElementById('invoiceTotal').value).toFixed(2);
        const clientCPF = document.getElementById('clientCPF').value || 'Não Informado';
        const clientPhone = document.getElementById('clientPhone').value || 'Não Informado';
        const transportMethod = document.getElementById('transportMethod').value;
        const shippingDate = document.getElementById('shippingDate').value;
        const invoiceDescription = document.getElementById('invoiceDescription').value || 'Prestação de Serviço de Logística (Cross Docking).';
        const invoiceNumber = Math.floor(Math.random() * 100000) + 1000;
        const issuanceDate = new Date().toLocaleDateString('pt-BR');

        // Geração do Conteúdo Simulado (Texto para o QR Code)
        const qrCodeText = `NF: ${invoiceNumber} | Cliente: ${clientName} | Valor: R$ ${invoiceTotal}`;

        // 1. Criar o HTML da Nota Fiscal para a pré-visualização
        // Adicionamos o id="invoiceContent" para que a função de impressão possa pegá-lo
        const invoiceHTML = `
            <div id="invoiceContent" class="invoice-content" style="border: 1px solid #ccc; padding: 20px; font-size: 10pt; background-color: white;">
                <h4 class="text-center" style="margin-bottom: 20px;">NOTA FISCAL ELETRÔNICA - NFS-e</h4>
                
                <table class="table table-bordered table-sm" style="margin-bottom: 10px; font-size: 10pt;">
                    <tr>
                        <td colspan="2"><strong>Número da NF:</strong> ${invoiceNumber}</td>
                        <td colspan="2"><strong>Data de Emissão:</strong> ${issuanceDate}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="background-color: #f8f9fa;"><strong>DADOS DO PRESTADOR DE SERVIÇO (EMITENTE)</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <strong>Empresa:</strong> 2° Logística - Cross Docking<br>
                            <strong>CNPJ:</strong> 12.345.678/9100-00<br>
                            <strong>Endereço:</strong> Rua da Logística, 123 - Cidade/UF
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="background-color: #f8f9fa;"><strong>DADOS DO TOMADOR DE SERVIÇO (DESTINATÁRIO)</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Cliente:</strong> ${clientName}</td>
                        <td colspan="2"><strong>CPF:</strong> ${clientCPF}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>E-mail:</strong> ${clientEmail}</td>
                        <td colspan="2"><strong>Telefone:</strong> ${clientPhone}</td>
                    </tr>
                </table>

                <h5 style="margin-top: 15px; margin-bottom: 10px;">DETALHES DA PRESTAÇÃO</h5>
                <table class="table table-bordered table-sm" style="margin-bottom: 15px; font-size: 10pt;">
                    <thead>
                        <tr style="background-color: #e9ecef;">
                            <th style="width: 70%;">Descrição do Serviço</th>
                            <th style="width: 30%; text-align: right;">Valor (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${invoiceDescription}</td>
                            <td style="text-align: right;">${invoiceTotal}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #fff3cd;">
                            <td><strong>TOTAL GERAL</strong></td>
                            <td style="text-align: right;"><strong>R$ ${invoiceTotal}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <h5 style="margin-top: 15px; margin-bottom: 10px;">INFORMAÇÕES ADICIONAIS</h5>
                <p style="font-size: 9pt;">
                    <strong>Método de Transporte:</strong> ${transportMethod} | 
                    <strong>Previsão de Envio:</strong> ${new Date(shippingDate).toLocaleDateString('pt-BR')}
                </p>

                <div class="text-center" style="margin-top: 25px;">
                    <div id="qrcode" style="display: inline-block;"></div>
                    <p style="font-size: 9pt; margin-top: 5px;">Chave de Acesso: ${qrCodeText}</p>
                </div>
                
                <div style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px;">
                    <p class="text-center text-muted" style="font-size: 8pt;">Documento Auxiliar de Nota Fiscal Eletrônica de Serviços (DANFSE)</p>
                </div>

            </div>
        `;

        // 2. Inserir o HTML e gerar o QR Code
        document.getElementById('initialPreviewText').style.display = 'none';
        const generatedContentDiv = document.getElementById('generatedContent');
        generatedContentDiv.innerHTML = invoiceHTML;
        
        // Limpa e gera o novo QR Code
        // Nota: O QR Code deve ser gerado APÓS a injeção do HTML, 
        // mas deve ser gerado no elemento que está DENTRO do generatedContent.
        new QRCode(document.getElementById("qrcode"), {
            text: qrCodeText,
            width: 128,
            height: 128,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // 3. Exibir o botão de impressão
        document.getElementById('printButtonWrapper').style.display = 'block'; 
    }

    // Função CORRIGIDA para imprimir somente a Nota Fiscal usando nova janela
    function printInvoice() {
        const printContent = document.getElementById('invoiceContent').outerHTML;
        
        // Estilos essenciais para a nova janela de impressão
        const printStyles = `
            <style>
                @page { size: A4; margin: 15mm; }
                body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                .invoice-content { 
                    width: 100%; 
                    border: none !important; 
                    padding: 0;
                    box-shadow: none;
                }
                .table, .table td, .table th {
                    border: 1px solid #000 !important;
                    font-size: 10pt;
                    color: #000;
                    margin: 0;
                    padding: 4px;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .text-center { text-align: center; }
                /* Oculta o QR Code se precisar economizar tinta */
                /* #qrcode { display: none; } */
            </style>
        `;

        // 1. Abrir uma nova janela
        const printWindow = window.open('', '_blank', 'height=600,width=800');
        
        // 2. Escrever o conteúdo da Nota Fiscal + estilos na nova janela
        printWindow.document.write('<html><head><title>Nota Fiscal</title>');
        printWindow.document.write(printStyles);
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        // 3. Esperar o carregamento (garantindo que tudo está pronto) e imprimir
        printWindow.onload = function() {
            printWindow.focus(); // Foca na nova janela (pode ajudar contra bloqueio)
            printWindow.print();
            // printWindow.close(); // Opcional: fechar a janela automaticamente após impressão/cancelamento
        };
    }
    
    // Anexa a função de geração ao evento de submit do formulário
    document.getElementById('invoiceForm').addEventListener('submit', generateInvoice);
    
    // -------------------------------
// FUNÇÃO PARA SALVAR A IMAGEM DA NF
// -------------------------------
function salvarImagemNF() {
    const nf = document.getElementById("invoiceContent");

    return html2canvas(nf).then(canvas => {
        const imagem = canvas.toDataURL("image/png");

        // Baixa automaticamente a imagem
        const link = document.createElement("a");
        link.href = imagem;
        link.download = "nota_fiscal.png";
        link.click();

        return imagem;
    });
}


// -------------------------------
// ADICIONAR APÓS GERAR A NOTA FISCAL
// -------------------------------

// Procure no final da função generateInvoice() esta linha:
// document.getElementById('printButtonWrapper').style.display = 'block';

// COLE ISSO LOGO ABAIXO DELA:
(function() {
    const oldGenerate = generateInvoice;

    generateInvoice = function(event) {

        // executa a função original
        oldGenerate(event);

        setTimeout(() => {
            if (!document.getElementById("invoiceContent")) return;

            salvarImagemNF().then(imagemBase64 => {

                // Coleta os dados do formulário
                const dados = {
                    numero: document.querySelector("#invoiceContent").innerText.match(/Número da NF:\s*(\d+)/)[1],
                    cliente: document.getElementById('clientName').value,
                    email: document.getElementById('clientEmail').value,
                    cpf: document.getElementById('clientCPF').value || "Não informado",
                    telefone: document.getElementById('clientPhone').value || "Não informado",
                    descricao: document.getElementById('invoiceDescription').value,
                    valor: document.getElementById('invoiceTotal').value,
                    envio: document.getElementById('shippingDate').value,
                    transporte: document.getElementById('transportMethod').value,
                    emissao: new Date().toLocaleDateString('pt-BR'),
                    imagem: imagemBase64
                };

                // Salva no localStorage para enviar ao view-orders.html
                localStorage.setItem("ultima_nota_fiscal", JSON.stringify(dados));

                // Redireciona automaticamente
                window.location.href = "view-orders.html";
            });

        }, 500);

    };
})();

</script>
    </body>
</html>