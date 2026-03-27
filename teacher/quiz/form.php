<?php
$page_title = 'Thêm Quiz - Giáo viên';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/models/quiz.php';
require_once dirname(__DIR__) . '/models/bai_hoc.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';

$quiz_model = new Quiz();
$bh_model   = new BaiHoc();
$kh_model   = new KhoaHoc();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;

// Lấy KH + BH của GV
$khoa_list = $kh_model->layKhoaHocCuaGiaoVien($nguoi_dung['id']);
$khoa_ids  = array_column($khoa_list, 'id');
$bh_all     = $bh_model->layTatCa();
$bh_gv      = array_filter($bh_all, fn($b) => in_array((int)($b['id_khoa_hoc'] ?? 0), $khoa_ids));

$thong_bao = '';

if ($is_edit) {
    $q = $quiz_model->layTheoId($id);
    if (!$q) { header('Location: index.php'); exit; }
    $khoa_check = $kh_model->layTheoId($q['id_khoa_hoc'] ?? 0);
    if (!$khoa_check || (int)$khoa_check['id_nguoi_tao'] !== (int)$nguoi_dung['id']) {
        header('Location: index.php'); exit;
    }
    $page_title = 'Sửa Quiz';
    $cau_hoi_list = $quiz_model->layCauHoiChiTiet($id);
    $form_data = [
        'tieu_de' => $q['tieu_de'],
        'mo_ta' => $q['mo_ta'],
        'id_bai_hoc' => $q['id_bai_hoc'],
        'thoi_gian_phut' => $q['thoi_gian_phut'],
        'diem_toi_da' => $q['diem_toi_da'],
    ];
} else {
    $cau_hoi_list = [];
    $form_data = [
        'tieu_de' => '',
        'mo_ta' => '',
        'id_bai_hoc' => isset($_GET['bai_hoc']) ? (int)$_GET['bai_hoc'] : '',
        'thoi_gian_phut' => 15,
        'diem_toi_da' => 100,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['tieu_de']        = trim($_POST['tieu_de'] ?? '');
    $form_data['mo_ta']          = trim($_POST['mo_ta'] ?? '');
    $form_data['id_bai_hoc']     = (int)($_POST['id_bai_hoc'] ?? 0);
    $form_data['thoi_gian_phut'] = (int)($_POST['thoi_gian_phut'] ?? 15);
    $form_data['diem_toi_da']    = (int)($_POST['diem_toi_da'] ?? 100);
    $cau_hoi_post                = $_POST['cau_hoi'] ?? [];

    if (empty($form_data['tieu_de'])) {
        $thong_bao = '<div class="alert alert-danger">Tiêu đề quiz không được để trống.</div>';
    } elseif ($form_data['id_bai_hoc'] <= 0) {
        $thong_bao = '<div class="alert alert-danger">Vui lòng chọn bài học.</div>';
    } else {
        // Verify BH belongs to GV
        $bh_check = $bh_model->layTheoId($form_data['id_bai_hoc']);
        if (!$bh_check) {
            $thong_bao = '<div class="alert alert-danger">Bài học không hợp lệ.</div>';
        } else {
            $khoa_check2 = $kh_model->layTheoId($bh_check['id_khoa_hoc']);
            if (!$khoa_check2 || (int)$khoa_check2['id_nguoi_tao'] !== (int)$nguoi_dung['id']) {
                $thong_bao = '<div class="alert alert-danger">Bạn không có quyền thêm quiz vào bài học này.</div>';
            } else {
                if ($is_edit) {
                    $quiz_model->sua($id, $form_data['tieu_de'], $form_data['mo_ta'],
                               $form_data['thoi_gian_phut'], $form_data['diem_toi_da']);
                    $quiz_id = $id;
                    $thong_bao = '<div class="alert alert-success">Cập nhật quiz thành công!</div>';
                } else {
                    $result = $quiz_model->them($form_data['id_bai_hoc'], $form_data['tieu_de'],
                                       $form_data['mo_ta'], $form_data['thoi_gian_phut'],
                                       $form_data['diem_toi_da']);
                    if (!$result['success']) {
                        $thong_bao = '<div class="alert alert-danger">' . $result['message'] . '</div>';
                    } else {
                        $quiz_id = $result['id'];
                        header("Location: form.php?id=$quiz_id&saved=1");
                        exit;
                    }
                }

                // Lưu câu hỏi
                if (!isset($thong_bao) || strpos($thong_bao, 'alert-danger') === false) {
                    $quiz_id = $is_edit ? $id : $quiz_id;

                    if ($is_edit) {
                        $old_questions = $quiz_model->layCauHoi($id);
                        foreach ($old_questions as $och) {
                            $quiz_model->xoaCauHoi($och['id']);
                        }
                    }

                    foreach ($cau_hoi_post as $ch_data) {
                        $noi_dung = trim($ch_data['noi_dung'] ?? '');
                        $dap_an_list = $ch_data['dap_an'] ?? [];
                        $dap_an_dung = isset($ch_data['dap_an_dung']) ? (int)$ch_data['dap_an_dung'] : -1;

                        if ($noi_dung === '' || count(array_filter($dap_an_list)) === 0) continue;

                        $db2 = Database::getInstance()->getConnection();
                        $stmt = $db2->prepare("INSERT INTO cau_hoi (id_quiz, noi_dung) VALUES (?, ?)");
                        $stmt->bind_param("is", $quiz_id, $noi_dung);
                        $stmt->execute();
                        $cau_hoi_id = $stmt->insert_id;

                        foreach ($dap_an_list as $idx => $da) {
                            $da = trim($da);
                            if ($da === '') continue;
                            $la_dung = ($idx === $dap_an_dung) ? 1 : 0;
                            $stmt2 = $db2->prepare("INSERT INTO dap_an (id_cau_hoi, noi_dung, la_dap_an_dung) VALUES (?, ?, ?)");
                            $stmt2->bind_param("isi", $cau_hoi_id, $da, $la_dung);
                            $stmt2->execute();
                        }
                    }

                    $cau_hoi_list = $quiz_model->layCauHoiChiTiet($quiz_id);
                    $thong_bao = '<div class="alert alert-success">Lưu quiz và câu hỏi thành công!</div>';
                }
            }
        }
    }
}

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="tv-topbar">
    <div>
        <h1 class="h4 mb-0">
            <i class="fas fa-<?php echo $is_edit ? 'pen' : 'plus'; ?> me-2 text-success"></i>
            <?php echo $page_title; ?>
        </h1>
        <p class="text-muted small mb-0">
            <a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
        </p>
    </div>
</div>

<?php echo $thong_bao; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card tv-stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-info-circle me-1 text-success"></i>Thông tin Quiz
            </div>
            <div class="card-body">
                <form method="POST" id="quiz-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiêu đề Quiz <span class="text-danger">*</span></label>
                        <input type="text" name="tieu_de" class="form-control"
                               value="<?php echo htmlspecialchars($form_data['tieu_de']); ?>"
                               placeholder="VD: Kiểm tra HTML cơ bản" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bài học <span class="text-danger">*</span></label>
                        <select name="id_bai_hoc" class="form-select" required>
                            <option value="">-- Chọn bài học --</option>
                            <?php
                            $current_khoa = '';
                            foreach ($bh_gv as $bh_item):
                                if ($bh_item['ten_khoa_hoc'] !== $current_khoa):
                                    if ($current_khoa !== '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($bh_item['ten_khoa_hoc']) . '">';
                                    $current_khoa = $bh_item['ten_khoa_hoc'];
                                endif;
                            ?>
                                <option value="<?php echo $bh_item['id']; ?>"
                                    <?php if ($form_data['id_bai_hoc'] == $bh_item['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($bh_item['tieu_de']); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_khoa !== '') echo '</optgroup>'; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="mo_ta" class="form-control" rows="2"
                                  placeholder="Mô tả quiz (không bắt buộc)"><?php
                                  echo htmlspecialchars($form_data['mo_ta']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Thời gian (phút)</label>
                            <input type="number" name="thoi_gian_phut" class="form-control" min="1"
                                   value="<?php echo $form_data['thoi_gian_phut']; ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Điểm tối đa</label>
                            <input type="number" name="diem_toi_da" class="form-control" min="1"
                                   value="<?php echo $form_data['diem_toi_da']; ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-save me-1"></i>Lưu Quiz
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card tv-stat-card">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list-check me-1 text-success"></i>Câu hỏi</span>
                <button type="button" class="btn btn-sm btn-success" id="btn-them-cau-hoi">
                    <i class="fas fa-plus me-1"></i>Thêm câu hỏi
                </button>
            </div>
            <div class="card-body" id="questions-container">
                <?php
                $so_cau_hoi = 0;
                if (!empty($cau_hoi_list)):
                    foreach ($cau_hoi_list as $ch):
                        $so_cau_hoi++;
                        $dap_an_dung_idx = -1;
                        foreach ($ch['dap_an'] as $di => $da):
                            if ($da['la_dap_an_dung']) { $dap_an_dung_idx = $di; break; }
                        endforeach;
                ?>
                    <div class="question-block mb-3 p-3 border rounded" data-question="<?php echo $so_cau_hoi; ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-success">Câu hỏi <?php echo $so_cau_hoi; ?></strong>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-question">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <textarea name="cau_hoi[<?php echo $so_cau_hoi; ?>][noi_dung]" class="form-control mb-2"
                                  rows="2" placeholder="Nội dung câu hỏi..."><?php
                                  echo htmlspecialchars($ch['noi_dung']); ?></textarea>
                        <div class="answers-container">
                            <?php foreach ($ch['dap_an'] as $ai => $da): ?>
                                <div class="input-group mb-1">
                                    <div class="input-group-text">
                                        <input type="radio" name="cau_hoi[<?php echo $so_cau_hoi; ?>][dap_an_dung]"
                                               value="<?php echo $ai; ?>"
                                               <?php if ($dap_an_dung_idx === $ai) echo 'checked'; ?>>
                                    </div>
                                    <input type="text" class="form-control"
                                           name="cau_hoi[<?php echo $so_cau_hoi; ?>][dap_an][]"
                                           value="<?php echo htmlspecialchars($da['noi_dung']); ?>"
                                           placeholder="Đáp án <?php echo $ai + 1; ?>">
                                    <button type="button" class="btn btn-outline-secondary btn-remove-answer">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-1 btn-add-answer">
                            <i class="fas fa-plus me-1"></i>Thêm đáp án
                        </button>
                    </div>
                <?php endforeach; endif; ?>

                <template id="question-template">
                    <div class="question-block mb-3 p-3 border rounded" data-question="__NUM__">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-success">Câu hỏi __NUM__</strong>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-question">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <textarea name="cau_hoi[__NUM__][noi_dung]" class="form-control mb-2"
                                  rows="2" placeholder="Nội dung câu hỏi..."></textarea>
                        <div class="answers-container">
                            <div class="input-group mb-1">
                                <div class="input-group-text">
                                    <input type="radio" name="cau_hoi[__NUM__][dap_an_dung]" value="0" checked>
                                </div>
                                <input type="text" class="form-control"
                                       name="cau_hoi[__NUM__][dap_an][]" placeholder="Đáp án 1">
                                <button type="button" class="btn btn-outline-secondary btn-remove-answer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="input-group mb-1">
                                <div class="input-group-text">
                                    <input type="radio" name="cau_hoi[__NUM__][dap_an_dung]" value="1">
                                </div>
                                <input type="text" class="form-control"
                                       name="cau_hoi[__NUM__][dap_an][]" placeholder="Đáp án 2">
                                <button type="button" class="btn btn-outline-secondary btn-remove-answer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-1 btn-add-answer">
                            <i class="fas fa-plus me-1"></i>Thêm đáp án
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var questionCount = <?php echo $so_cau_hoi; ?>;
    var container = document.getElementById('questions-container');

    document.getElementById('btn-them-cau-hoi').addEventListener('click', function() {
        questionCount++;
        var tpl = document.getElementById('question-template').innerHTML;
        tpl = tpl.replace(/__NUM__/g, questionCount);
        container.insertAdjacentHTML('beforeend', tpl);
        rebindEvents();
    });

    function rebindEvents() {
        document.querySelectorAll('.btn-remove-question').forEach(function(btn) {
            btn.onclick = function() {
                var block = this.closest('.question-block');
                if (confirm('Xóa câu hỏi này?')) block.remove();
            };
        });
        document.querySelectorAll('.btn-remove-answer').forEach(function(btn) {
            btn.onclick = function() {
                var row = this.closest('.input-group');
                var container = row.parentElement;
                if (container.querySelectorAll('.input-group').length > 2) {
                    row.remove();
                } else {
                    alert('Cần ít nhất 2 đáp án.');
                }
            };
        });
        document.querySelectorAll('.btn-add-answer').forEach(function(btn) {
            btn.onclick = function() {
                var qBlock = this.closest('.question-block');
                var qNum = qBlock.dataset.question;
                var answersContainer = qBlock.querySelector('.answers-container');
                var idx = answersContainer.querySelectorAll('.input-group').length;
                var html = '<div class="input-group mb-1">' +
                    '<div class="input-group-text">' +
                    '<input type="radio" name="cau_hoi[' + qNum + '][dap_an_dung]" value="' + idx + '">' +
                    '</div>' +
                    '<input type="text" class="form-control" name="cau_hoi[' + qNum + '][dap_an][]" ' +
                    'placeholder="Đáp án ' + (idx + 1) + '">' +
                    '<button type="button" class="btn btn-outline-secondary btn-remove-answer">' +
                    '<i class="fas fa-times"></i></button></div>';
                answersContainer.insertAdjacentHTML('beforeend', html);
                rebindEvents();
            };
        });
    }

    rebindEvents();
})();
</script>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
