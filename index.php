<style>
    #preview {
        display: flex;
        flex-wrap: wrap;
    }

    #preview>div {
        margin: 10px;
    }

    #preview img,
    #preview video {
        display: block;
        width: 250px;
        height: 250px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        display: block;
        margin: auto;
        width: 80%;
        max-width: 800px;
        max-height: 80%;
        margin-top: 120px;
    }

    .close {
        color: white;
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }
</style>

<form id="myForm" method="POST" enctype="multipart/form-data">
    <input type="text" name="myText">
    <input type="file" name="myFiles[]" id="fileInput" multiple>
    <div id="preview"></div>
    <button type="submit">Enviar</button>
</form>

<div id="myModal" class="modal">
    <span class="close">&times;</span>
    <img id="modalImg" class="modal-content">
    <button id="prevBtn">&lt;</button>
    <button id="nextBtn">&gt;</button>
</div>

<script>
    const fileInput = document.getElementById("fileInput");
    const preview = document.getElementById("preview");
    let formDataArray = [];

    fileInput.addEventListener("change", () => {
        const files = Array.from(fileInput.files);
        files.forEach(async (file, index) => {
            const formData = new FormData();
            formData.append("myFiles[]", file);
            const reader = new FileReader();
            reader.onload = () => {
                const fileType = file.type.split("/")[0];
                const previewItemId = "previewItem_" + Date.now();
                let previewItem;
                if (fileType === "image") {
                    previewItem = document.createElement("img");
                } else if (fileType === "video") {
                    previewItem = document.createElement("video");
                    previewItem.setAttribute("controls", "");
                } else {
                    return;
                }
                previewItem.src = reader.result;
                previewItem.id = previewItemId;
                const deleteBtn = document.createElement("button");
                deleteBtn.textContent = "Excluir";
                deleteBtn.addEventListener("click", () => {
                    const previewItemNode = document.getElementById(previewItemId).parentNode;
                    preview.removeChild(previewItemNode);
                    const remainingFiles = Array.from(preview.children).map(
                        (item) => item.firstChild
                    );
                    if (remainingFiles.length === 0) {
                        fileInput.value = "";
                    }
                    // remove o arquivo correspondente do objeto formData
                    formData.delete("myFiles[]", index);
                    // remove o objeto formData da matriz se não houver mais arquivos
                    if (formData.getAll("myFiles[]").length === 0) {
                        formDataArray = formDataArray.filter((item) => item !== formData);
                    }
                });
                const previewWrapper = document.createElement("div");
                previewWrapper.appendChild(previewItem);
                previewWrapper.appendChild(deleteBtn);
                preview.appendChild(previewWrapper);
            };
            reader.readAsDataURL(file);
            formDataArray.push(formData); // adiciona o objeto FormData à matriz
        });
    });

    // QUANDO CLICADO EM CIMA DA IMAGEM ELA É AMPLIADA
    preview.addEventListener("click", (event) => {
        const clickedItem = event.target;
        const fileType = clickedItem.tagName.toLowerCase();
        if (fileType === "img") {
            const modalImg = document.getElementById("modalImg");
            modalImg.src = clickedItem.src;
            const modal = document.getElementById("myModal");
            modal.style.display = "block";
        }
    });

    // Define a função para atualizar a imagem ampliada no modal
    const updateModalImg = (index) => {
        const modalImg = document.getElementById("modalImg");
        const previewItems = Array.from(document.querySelectorAll("#preview>div>img"));
        currentIndex = (index + previewItems.length) % previewItems.length; // Lógica de loop para a visualização circular
        modalImg.src = previewItems[currentIndex].src;
    };

    // Adicionando botões de visualização anterior e posterior ao modal
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    let currentIndex = 0;

    // Adiciona ouvintes de eventos aos botões de visualização anterior e posterior
    prevBtn.addEventListener("click", () => {
        updateModalImg(currentIndex - 1);
    });

    nextBtn.addEventListener("click", () => {
        updateModalImg(currentIndex + 1);
    });

    // FECHAR MODAL
    const closeModalBtn = document.querySelector(".close");
    closeModalBtn.addEventListener("click", () => {
        const modal = document.getElementById("myModal");
        const modalImg = document.getElementById("modalImg");
        modal.style.display = "none";
        modalImg.src = "";
    });

    // ENVIANDO PARA O SERVIDOR
    const form = document.getElementById("myForm");

    form.addEventListener("submit", event => {
        event.preventDefault();
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "upload.php");
        const allFormData = new FormData(); // cria um novo objeto FormData para armazenar todos os objetos FormData
        formDataArray.forEach(formData => {
            for (const pair of formData.entries()) {
                allFormData.append(pair[0], pair[1]); // adiciona cada par de chave-valor do objeto FormData à matriz
            }
        });

        for (const [key, value] of allFormData.entries()) {
            if (value instanceof File) {
                console.log(`Nome do arquivo: ${value.name}`);
            }
        }

        xhr.send(allFormData); // envia todos os arquivos adicionados para o servidor
    });
</script>