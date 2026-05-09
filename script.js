// دالة تبديل الوضع الليلي
function toggleNightMode() {
    const isNight = document.body.classList.toggle('night-mode');
    // حفظ الحالة في المتصفح
    localStorage.setItem('theme', isNight ? 'dark' : 'light');
}

// الكود الذي يعمل تلقائياً عند فتح أي صفحة
window.onload = function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('night-mode');
    }
};

// Function for Gallery Filtering
function filterRegions() {
    let filterValue = document.getElementById("regionFilter").value;
    let cards = document.querySelectorAll(".card");
    
    cards.forEach(card => {
        if (filterValue === "all" || card.getAttribute("data-category") === filterValue) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}