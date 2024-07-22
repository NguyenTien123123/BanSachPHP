function updateQuantity(sachID, increment) {
    var quantityInput = document.querySelector(`#quantity_${sachID}`);
    var currentQuantity = parseInt(quantityInput.value);
    var newQuantity = increment ? currentQuantity + 1 : currentQuantity - 1;

    // Đảm bảo số lượng không rơi vào giá trị không hợp lệ
    newQuantity = newQuantity < 1 ? 1 : newQuantity;
    quantityInput.value = newQuantity;

    // Cập nhật Subtotal cho sản phẩm này
    var pricePerUnit = parseFloat(document.querySelector(`#price_${sachID}`).textContent.replace(/[, VND]/g, ''));
    var newSubtotal = newQuantity * pricePerUnit;
    document.querySelector(`#subtotal_${sachID}`).textContent = `${newSubtotal.toFixed(2)} VND`;

    // Gửi yêu cầu AJAX để cập nhật cơ sở dữ liệu
    fetch('update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&bookId=${sachID}&quantity=${newQuantity}`
    })
        .then(response => response.text())
        .then(data => {
            console.log('Response:', data);
            updateCartTotal();
        })
        .catch(error => console.error('Error:', error));
}
function deleteItem(sachID) {
    if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này không?")) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&bookId=${sachID}`
        })
            .then(response => response.text())
            .then(data => {
                console.log('Deleted:', data);
                document.getElementById(`row_${sachID}`).remove(); // Xóa dòng sản phẩm khỏi bảng
                updateCartTotal();
            })
            .catch(error => console.error('Error:', error));
    }
}

function updateCartTotal() {
    var total = 0;
    document.querySelectorAll('[id^="subtotal_"]').forEach(item => {
        total += parseFloat(item.textContent.replace(/[, VND]/g, ''));
    });
    document.getElementById('total-price').textContent = `${total.toFixed(2)} VND`;
}

