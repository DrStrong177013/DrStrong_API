// loading.js
window.addEventListener('load', function() {
    // Ẩn màn hình chờ sau 1 giây tối thiểu
    setTimeout(() => {
        document.getElementById('loadingOverlay').style.display = 'none';
    }, 500); // Thay đổi giá trị này để tùy chỉnh thời gian tối thiểu
});
