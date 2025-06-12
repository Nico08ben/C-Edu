const toast = document.querySelector(".toast");
// Select the close icon specifically within the toast element
const closeIcon = toast.querySelector(".close"); 
const progress = document.querySelector(".progress");

// Function to show the toast
function showToast() {
    toast.classList.add("active");
    progress.classList.add("active");

    setTimeout(() => {
        toast.classList.remove("active");
        // Also remove progress bar 'active' class when toast disappears automatically
        progress.classList.remove("active"); 
    }, 5000); // Toast disappears after 5 seconds
}

// Event listener for the close icon
closeIcon.addEventListener("click", () => {
    toast.classList.remove("active");
    progress.classList.remove("active"); // Immediately remove active from progress when closing manually
});

// Expose showToast to be called from other scripts
window.showToastNotification = showToast;