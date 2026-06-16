let mobileButton = document.getElementById("mobileButton");
let myDropdown = document.getElementById("myDropdown");

mobileButton.addEventListener("click", function () {
    console.log("ok");
    myDropdown.classList.toggle("mobileMenu");
});
