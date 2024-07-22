function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.classList.add('notification', type);
    notification.textContent = message;
    document.body.appendChild(notification);
    notification.classList.add('show');

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 500);
    }, 3000);
}

function addToCart(sachID) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "add_to_cart.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (this.status === 200) {
            const message = this.responseText;
            if (message.includes('đã được thêm vào giỏ hàng')) {
                showNotification(message, 'success');
            } else {
                showNotification(message, 'error');
            }
        }
    };
    xhr.send("sachID=" + sachID + "&quantity=1");
}

function requireLogin() {
    alert('Bạn cần đăng nhập để thực hiện chức năng này!');
    window.location.href = 'login.php';
}

function showCart() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_cart.php', true);
    xhr.onload = function () {
        if (this.status == 200) {
            document.getElementById('cartContent').innerHTML = this.responseText;
            document.getElementById('cartModal').style.display = 'block';
        }
    }
    xhr.send();
}

// Hàm lọc sách theo thể loại
document.addEventListener('DOMContentLoaded', function () {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const bookItems = document.querySelectorAll('.book-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            const filter = this.getAttribute('data-filter');

            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            bookItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Load notifications when the dropdown is clicked
    $('#notificationsDropdown').on('click', function () {
        if (isLoggedIn) {
            $.ajax({
                url: 'get_notifications.php',
                method: 'GET',
                success: function (data) {
                    $('#notificationContent').html(data);
                }
            });
        }
    });
});
