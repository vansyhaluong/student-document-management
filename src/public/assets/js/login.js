// =====================================================================
// FILE: assets/js/login.js
// MỤC ĐÍCH: Cho phép người dùng bấm icon con mắt để hiện/ẩn mật khẩu
//           đang nhập trong ô "Mật khẩu" ở trang đăng nhập.
// =====================================================================

// Hàm được gọi khi bấm vào icon con mắt
function hienAnMatKhau() {
  const oMatKhau = document.getElementById('mat-khau');       // Lấy ô nhập mật khẩu
  const oMat = document.getElementById('icon-mat');           // Lấy icon con mắt
  if (oMatKhau.type === 'password') {                         // Nếu đang ẩn (type=password)
    oMatKhau.type = 'text';                                    // Chuyển sang hiện chữ (type=text)
    oMat.textContent = '🙈';                                   // Đổi icon thành mắt nhắm
  } else {                                                     // Nếu đang hiện
    oMatKhau.type = 'password';                                // Chuyển lại thành ẩn
    oMat.textContent = '👁️';                                  // Đổi icon thành mắt mở
  }
}
