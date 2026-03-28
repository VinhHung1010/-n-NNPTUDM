<?php
$conn = new mysqli('localhost', 'root', '', 'elearning_db');
if ($conn->connect_error) {
    echo 'Error: ' . $conn->connect_error;
    exit;
}

$sql = "CREATE TABLE IF NOT EXISTS yeu_thich (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_dung INT NOT NULL,
    id_khoa_hoc INT NOT NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_khoa_hoc) REFERENCES khoa_hoc(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (id_nguoi_dung, id_khoa_hoc)
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo 'Table yeu_thich created successfully!';
} else {
    echo 'Error: ' . $conn->error;
}
$conn->close();
