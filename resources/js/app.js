import './bootstrap';
import 'laravel-datatables-vite';

const userId = document.querySelector('meta[name="user-id"]')?.content;

if (userId) {
    let toastCount = 0;

    window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            console.log(notification);
            const toast = document.createElement("div");
            // toast.innerText = notification.message;
            // toast.innerText = notification.note_id;
            toast.innerText = `${notification.message} (ID: ${notification.note_id})`;
            toast.style.position = "fixed";
            toast.style.top = (20 + toastCount * 60) + "px";  // offset each toast
            toast.style.right = "20px";
            toast.style.background = "#16a34a";
            toast.style.color = "white";
            toast.style.padding = "10px 20px";
            toast.style.borderRadius = "6px";
            toast.style.zIndex = 9999;
            toast.style.transition = "all 0.3s ease";

            document.body.appendChild(toast);
            toastCount++;

            setTimeout(() => {
                toast.remove();
                toastCount--;
            }, 3000);
        });
}