// ===========================================
// CONFIGURAÇÕES GLOBAIS E FUNÇÕES GERAIS
// ===========================================

const ANIMATION_DURATION = 300;
const BACKEND_STORE_INVOICE_URL = 'processa_nota_fiscal.php';


// ===========================================
// FUNÇÕES SLIDE
// ===========================================

const slideUp = (target, duration = ANIMATION_DURATION) => {
    const { parentElement } = target;
    parentElement.classList.remove("open");
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = `${duration}ms`;
    target.style.boxSizing = "border-box";
    target.style.height = `${target.offsetHeight}px`;
    target.offsetHeight;
    target.style.overflow = "hidden";
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    window.setTimeout(() => {
        target.style.display = "none";
        target.style.removeProperty("height");
        target.style.removeProperty("padding-top");
        target.style.removeProperty("padding-bottom");
        target.style.removeProperty("margin-top");
        target.style.removeProperty("margin-bottom");
        target.style.removeProperty("overflow");
        target.style.removeProperty("transition-duration");
        target.style.removeProperty("transition-property");
    }, duration);
};

const slideDown = (target, duration = ANIMATION_DURATION) => {
    const { parentElement } = target;
    parentElement.classList.add("open");
    target.style.removeProperty("display");
    let { display } = window.getComputedStyle(target);
    if (display === "none") display = "block";
    target.style.display = display;
    const height = target.offsetHeight;
    target.style.overflow = "hidden";
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.offsetHeight;
    target.style.boxSizing = "border-box";
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = `${duration}ms`;
    target.style.height = `${height}px`;
    target.style.removeProperty("padding-top");
    target.style.removeProperty("padding-bottom");
    target.style.removeProperty("margin-top");
    target.style.removeProperty("margin-bottom");
    window.setTimeout(() => {
        target.style.removeProperty("height");
        target.style.removeProperty("overflow");
        target.style.removeProperty("transition-duration");
        target.style.removeProperty("transition-property");
    }, duration);
};

const slideToggle = (target, duration = ANIMATION_DURATION) => {
    if (window.getComputedStyle(target).display === "none")
        return slideDown(target, duration);
    return slideUp(target, duration);
};

// ===========================================
// CLASSE POPPER.JS
// ===========================================

class PopperObject {
    instance = null;
    reference = null;
    popperTarget = null;

    constructor(reference, popperTarget) {
        this.init(reference, popperTarget);
    }

    init(reference, popperTarget) {
        this.reference = reference;
        this.popperTarget = popperTarget;
        
        if (typeof Popper !== 'undefined') {
             this.instance = Popper.createPopper(this.reference, this.popperTarget, {
                 placement: "right",
                 strategy: "fixed",
                 resize: true,
                 modifiers: [
                     { name: "computeStyles", options: { adaptive: false } },
                     { name: "flip", options: { fallbackPlacements: ["left", "right"] } }
                 ]
             });
        }

        document.addEventListener(
            "click",
            (e) => this.clicker(e, this.popperTarget, this.reference),
            false
        );

        const ro = new ResizeObserver(() => {
             if (this.instance) this.instance.update();
        });

        ro.observe(this.popperTarget);
        ro.observe(this.reference);
    }

    clicker(event, popperTarget, reference) {
        const SIDEBAR_EL = document.getElementById("sidebar"); // Busca o elemento no momento do clique
        if (
            SIDEBAR_EL && SIDEBAR_EL.classList.contains("collapsed") &&
            !popperTarget.contains(event.target) &&
            !reference.contains(event.target)
        ) {
            this.hide();
        }
    }

    hide() {
        if (this.instance) {
            this.instance.state.elements.popper.style.visibility = "hidden";
        }
    }
    
    show() {
        if (this.instance) {
            this.instance.state.elements.popper.style.visibility = "visible";
        }
    }
}

class Poppers {
    subMenuPoppers = [];

    constructor() {
        this.init();
    }

    init() {
        const SUB_MENU_ELS = document.querySelectorAll(".menu > ul > .menu-item.sub-menu");
        
        SUB_MENU_ELS.forEach((element) => {
            this.subMenuPoppers.push(
                new PopperObject(element, element.lastElementChild)
            );
            this.closePoppers();
        });
    }

    togglePopper(popperTarget) {
        const popperInstance = this.subMenuPoppers.find(p => p.popperTarget === popperTarget);
        
        if (popperInstance) {
            if (window.getComputedStyle(popperTarget).visibility === "hidden") {
                popperInstance.show();
            } else {
                popperInstance.hide();
            }
        }
    }

    updatePoppers() {
        this.subMenuPoppers.forEach((element) => {
            if (element.instance) {
                 element.instance.state.elements.popper.style.display = "none";
                 element.instance.update();
            }
        });
    }

    closePoppers() {
        this.subMenuPoppers.forEach((element) => {
            element.hide();
        });
    }
}


// =======================================================
// LÓGICA PRINCIPAL (Executada APÓS o carregamento do DOM)
// =======================================================
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Referências Comuns ---
    const SIDEBAR_EL = document.getElementById("sidebar");
    const OVERLAY_EL = document.getElementById("overlay");
    const PoppersInstance = typeof Popper !== 'undefined' ? new Poppers() : null;
    
    // --- 1. LÓGICA DO SIDEBAR ---
    
    const btnToggle = document.getElementById('btn-toggle');
    const btnCollapse = document.getElementById('btn-collapse');
    const collapseIcon = document.getElementById('collapse-icon');

    // Função para alternar a sidebar no mobile
    btnToggle?.addEventListener('click', (e) => {
        e.preventDefault();
        SIDEBAR_EL.classList.toggle('toggled');
        OVERLAY_EL.classList.toggle('active');
    });

    OVERLAY_EL?.addEventListener('click', () => {
        SIDEBAR_EL.classList.remove('toggled');
        OVERLAY_EL.classList.remove('active');
    });

    // Função para colapsar/expandir a sidebar no desktop
    btnCollapse?.addEventListener('click', () => {
        SIDEBAR_EL.classList.toggle('collapsed');
        if (SIDEBAR_EL.classList.contains('collapsed')) {
            collapseIcon.classList.remove('fa-angle-double-left');
            collapseIcon.classList.add('fa-angle-double-right');
            PoppersInstance?.closePoppers(); // Fecha pop-ups ao colapsar
        } else {
            collapseIcon.classList.remove('fa-angle-double-right');
            collapseIcon.classList.add('fa-angle-double-left');
        }
    });


    // --- 2. LÓGICA DO SISTEMA DE GERAÇÃO DE NOTA FISCAL ---

    // Referências dos Inputs Principais (ID)
    const form = document.getElementById('invoiceForm');
    const clientNameInput = document.getElementById('clientName');
    const clientEmailInput = document.getElementById('clientEmail'); 
    const totalInput = document.getElementById('invoiceTotal');
    const descriptionInput = document.getElementById('invoiceDescription');
    const previewContainer = document.getElementById('generatedContent');
    const initialPreviewMessage = document.querySelector('#invoicePreview .text-center');
    const generateBtn = document.getElementById('generateBtn');

    // Referências dos Inputs Adicionais (ID)
    const clientCPFInput = document.getElementById('clientCPF');
    const clientPhoneInput = document.getElementById('clientPhone');
    const transportMethodInput = document.getElementById('transportMethod');
    const shippingDateInput = document.getElementById('shippingDate');


    /**
     * Gera o QR Code na div específica.
     */
    function generateQRCode(data) {
        // CUIDADO: O HTML precisa ter uma div com id="qrcodeContainer" DENTRO do generatedContent. 
        // O código da pré-visualização abaixo já garante isso.
        const qrcodeContainer = document.getElementById('qrcodeContainer');
        
        if (qrcodeContainer) {
            qrcodeContainer.innerHTML = ''; // Limpa antes de gerar
        } else {
            return;
        }

        if (typeof QRCode !== 'undefined') {
            new QRCode(qrcodeContainer, {
                text: data,
                width: 100,
                height: 100,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H 
            });
        }
    }


    /**
     * Gera e atualiza o HTML da Nota Fiscal na área de pré-visualização.
     * @returns {object} Retorna os dados completos da Nota Fiscal gerada.
     */
    function generateInvoicePreview() {
        // --- Lógica de Geração dos Dados ---
        const clientName = clientNameInput.value || "Nome do Cliente (Faltando)";
        const clientEmail = clientEmailInput.value || "email@exemplo.com"; 
        const totalValue = parseFloat(totalInput.value || 0).toFixed(2);
        const totalValueFormatted = totalValue.replace('.', ','); 
        const description = descriptionInput.value || "Serviços genéricos.";
        
        // Dados exclusivos da NF
        const invoiceNumber = 'NF' + Math.floor(Math.random() * 1000000);
        const accessKey = '432511' + Math.random().toString(36).substring(2, 12).toUpperCase();
        const issueDate = new Date().toLocaleDateString('pt-BR');

        if (initialPreviewMessage) {
            initialPreviewMessage.style.display = 'none';
        }

        // --- Geração do HTML (Status PENDENTE usando bg-secondary - cinza/neutro) ---
        const invoiceHTML = `
             <div class="border-bottom pb-3 mb-3">
                 <div class="d-flex justify-content-between align-items-center">
                     <div>
                         <h4 class="mb-0" style="color: #0c1e35;">FATURA #${invoiceNumber}</h4>
                         <small class="text-muted">Cliente: ${clientName}</small>
                     </div>
                     <h3 style="color: #5d8cfd;">R$ ${totalValueFormatted}</h3>
                 </div>
             </div>
             <h5 class="mt-4" style="color: #0c1e35;">Itens Faturados:</h5>
             <p class="text-muted">${description}</p>
             
             <table class="table table-sm mt-3">
                 <tbody>
                     <tr><th>Emitida em:</th><td>${issueDate}</td></tr>
                     <tr><th>E-mail:</th><td>${clientEmail}</td></tr>
                     <tr><th>Status:</th><td><span class="badge bg-secondary">PENDENTE</span></td></tr>
                 </tbody>
             </table>
             
             <div class="d-flex justify-content-between align-items-end mt-4 pt-3 border-top">
                 
                 <div>
                     <h5 style="color: #0c1e35;">Chave de Accesso:</h5>
                     <small class="text-muted d-block">${accessKey}</small>
                    
                 </div>

                 <div class="text-center">
                     <div id="qrcodeContainer" style="width: 100px; height: 100px; margin: auto;"></div> 
                     <small class="text-muted">Código QR</small>
                 </div>
             </div>
           `;
        
        previewContainer.innerHTML = invoiceHTML;
        
        // Gera o QR Code com a chave de acesso
        generateQRCode(accessKey);
        
        // Retorna todos os dados para a função de registro no DB
        return {
            invoiceNumber,
            clientName,
            clientEmail,
            totalValue: parseFloat(totalValue),
            description,
            accessKey,
            issueDate,
            invoiceHTML
        };
    }
    
    /**
     * Função que simula o envio da Nota Fiscal para o Backend para armazenamento.
     */
    function storeInvoiceInDB(invoiceData) {
        
        // Coletando os valores dos inputs adicionais
        const clientCPF = clientCPFInput?.value || 'N/A';
        const clientPhone = clientPhoneInput?.value || 'N/A';
        const transportMethod = transportMethodInput?.value || 'Digital';
        const shippingDate = shippingDateInput?.value || new Date().toISOString().slice(0, 10); 
        
        // ESTRUTURA DOS DADOS PARA O BACKEND
        const dbData = {
            email_cliente: invoiceData.clientEmail, 
            nome_cliente: invoiceData.clientName,
            valor_total_produto: invoiceData.totalValue, 
            
            cpf_cliente: clientCPF, 
            numero_telefone: clientPhone, 
            meio_transporte: transportMethod, 
            data_envio: shippingDate, 
            
            html_completo: invoiceData.invoiceHTML, 
            chave_acesso: invoiceData.accessKey,
            
            numero_nota: invoiceData.invoiceNumber,
            data_emissao_js: invoiceData.issueDate,
        };
    
        // Feedback visual de carregamento
        const originalBtnHTML = generateBtn.innerHTML;
        generateBtn.innerHTML = '<i class="fas fa-database fa-spin" style="margin-right: 10px;"></i> Registrando...';
        generateBtn.disabled = true;
        
        // Simulação de Chamada para o backend (Fetch/AJAX)
        fetch(BACKEND_STORE_INVOICE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dbData)
        })
        .then(response => {
            // Simulação de sucesso para fins de demonstração (Remova esta linha se seu PHP retornar 404/500)
            // return { ok: true, json: () => Promise.resolve({ id: invoiceData.invoiceNumber }) }; 
            
            if (!response.ok) {
                throw new Error(`Erro HTTP ${response.status}. Verifique se '${BACKEND_STORE_INVOICE_URL}' está rodando.`);
            }
            return response.json();
        })
        .then(data => {
            alert(`✅ Sucesso! Nota Fiscal registrada no banco de dados. ID: ${data.id || invoiceData.invoiceNumber}`);
            
            // Substituído 'btn-success' por 'btn-primary' para manter a cor principal
            generateBtn.innerHTML = '<i class="fas fa-check" style="margin-right: 10px;"></i> Nota Registrada!';
            // Removida a classe 'btn-success' e mantida 'btn-primary'
            
            setTimeout(() => {
                generateBtn.innerHTML = originalBtnHTML;
                generateBtn.disabled = false;
                // Não precisa substituir a classe se a cor neutra for a 'btn-primary'
            }, 3000); 

        })
        .catch((error) => {
            console.error('Falha ao registrar a Nota Fiscal:', error);
            alert(`❌ Falha ao registrar a nota fiscal. Verifique a URL do Backend e o console para detalhes: ${error.message}`);
            
            // Reverte o estado do botão para ERRO, usando 'btn-secondary' (cinza) para evitar o vermelho
            generateBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erro ao Registrar';
            generateBtn.disabled = false; 
            // Substituído 'btn-danger' por 'btn-secondary' para ser neutro
            generateBtn.classList.replace('btn-primary', 'btn-secondary'); 
            
            setTimeout(() => {
                generateBtn.innerHTML = originalBtnHTML;
                generateBtn.classList.replace('btn-secondary', 'btn-primary'); // Retorna ao primário
            }, 3000);
        });
    }


    // ====================================
    // 3. EVENT LISTENERS DO FORMULÁRIO
    // ====================================

    // Array de inputs para observar
    const inputsToWatch = [
        clientNameInput, clientEmailInput, totalInput, descriptionInput,
        clientCPFInput, clientPhoneInput, transportMethodInput, shippingDateInput
    ];

    // Adiciona o listener de atualização automática do preview
    inputsToWatch.forEach(input => {
        // Só adiciona o listener se o elemento existir
        input?.addEventListener('input', generateInvoicePreview);
        input?.addEventListener('change', generateInvoicePreview); 
    });


    // Listener principal para o formulário (no clique do botão 'submit')
    form?.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();

        // A validação do Bootstrap ('was-validated') continua a adicionar bordas.
        // Se você quiser remover as bordas vermelhas/verdes padrão, precisará
        // customizar o CSS de '.form-control:valid' e '.form-control:invalid'.
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // 1. Gera o preview e obtém os dados da nota
        const invoiceData = generateInvoicePreview();
        
        // 2. Chama a função para armazenar a nota no BD
        storeInvoiceInDB(invoiceData);

        form.classList.remove('was-validated');
    });

    // Chamada inicial para carregar o preview assim que a página carrega
    if (generateBtn) {
        setTimeout(() => {
            generateInvoicePreview(); 
        }, 50);
    }
});