const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('excel_file');
const selectFileBtn = document.getElementById('select-file-btn');
const uploadBtn = document.querySelector('.btn-upload');
const progressArea = document.querySelector('.progress-area');

let uploadTimeout;
let uploadStarted = false;

// Click to open file selector
selectFileBtn.addEventListener('click', () => {
    fileInput.click();
});

// Drag and drop file handler
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag-over');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    fileInput.files = e.dataTransfer.files;
    handleFileChange();
});

// File input change handler
fileInput.addEventListener('change', handleFileChange);

function handleFileChange() {
    const file = fileInput.files[0];
    if (file) {
        displayProgress(file);
        uploadBtn.classList.add('enabled');
        uploadBtn.disabled = false;
        uploadStarted = true;
    }
}

function displayProgress(file) {
    const fileName = file.name;
    const progressBox = document.createElement('div');
    progressBox.classList.add('progress-box');

    const fileIcon = document.createElement('img');
    fileIcon.src = 'https://img.icons8.com/color/48/000000/microsoft-excel-2019.png'; // icon excel

    const fileNameElem = document.createElement('span');
    fileNameElem.classList.add('file-name');
    fileNameElem.textContent = fileName;

    const progressBarContainer = document.createElement('div');
    progressBarContainer.classList.add('progress-bar-container');

    const progressBar = document.createElement('div');
    progressBar.classList.add('progress-bar');

    const percentageElem = document.createElement('span');
    percentageElem.classList.add('percentage');
    percentageElem.textContent = '0%';

    progressBox.appendChild(fileIcon);
    progressBox.appendChild(fileNameElem);
    progressBarContainer.appendChild(progressBar);
    progressBox.appendChild(progressBarContainer);
    progressBox.appendChild(percentageElem);
    progressArea.appendChild(progressBox);

    let uploaded = 0;
    const totalSize = file.size;
    const uploadSpeed = 2048; // 2kb per second

    uploadTimeout = setInterval(() => {
        if (uploaded < totalSize) {
            uploaded += uploadSpeed;
            const percentage = Math.min((uploaded / totalSize) * 100, 100);
            progressBar.style.width = `${percentage}%`;
            percentageElem.textContent = `${Math.floor(percentage)}%`;
        } else {
            clearInterval(uploadTimeout);
            progressBar.style.width = '100%';
            percentageElem.textContent = '100%';
        }
    }, 1000);
}
