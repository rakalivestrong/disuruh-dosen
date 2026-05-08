<?php
// ============================================
// HELPER KIRIM EMAIL DENGAN PHPMAILER
// ============================================

require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../config/email.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Kirim email notifikasi beasiswa
 * @param string $toEmail    - Email tujuan
 * @param string $toName     - Nama penerima
 * @param string $subject    - Subject email
 * @param string $body       - Body HTML email
 * @return bool
 */
function kirimEmail($toEmail, $toName, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        // Pengirim & Penerima
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // Konten
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Gagal kirim email ke $toEmail: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template email diterima
 */
function templateEmailDiterima($nama, $namaBeasiswa, $nominal) {
    return "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9f9f9; border-radius: 12px; overflow: hidden;'>
        <div style='background: linear-gradient(135deg, #10b981, #059669); padding: 30px; text-align: center;'>
            <h1 style='color: white; margin: 0; font-size: 28px;'>🎉 Selamat!</h1>
            <p style='color: rgba(255,255,255,0.9); margin: 8px 0 0;'>Pendaftaran Beasiswa Diterima</p>
        </div>
        <div style='padding: 30px; background: white;'>
            <p style='font-size: 16px; color: #333;'>Halo, <strong>$nama</strong>!</p>
            <p style='color: #555; line-height: 1.6;'>
                Kami dengan bangga memberitahukan bahwa pendaftaran beasiswa kamu telah 
                <strong style='color: #10b981;'>DITERIMA</strong>. 
                Selamat atas pencapaian ini! 🎓
            </p>
            <div style='background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 20px; margin: 20px 0;'>
                <h3 style='margin: 0 0 12px; color: #065f46;'>Detail Beasiswa</h3>
                <table style='width: 100%;'>
                    <tr>
                        <td style='color: #666; padding: 5px 0;'>Nama Beasiswa</td>
                        <td style='font-weight: bold; color: #333;'>$namaBeasiswa</td>
                    </tr>
                    <tr>
                        <td style='color: #666; padding: 5px 0;'>Dana Beasiswa</td>
                        <td style='font-weight: bold; color: #10b981; font-size: 18px;'>$nominal / bulan</td>
                    </tr>
                </table>
            </div>
            <p style='color: #555; line-height: 1.6;'>
                Silakan login ke sistem untuk melihat informasi lebih lanjut dan langkah berikutnya.
            </p>
            <div style='text-align: center; margin: 24px 0;'>
                <a href='http://localhost/project' 
                   style='display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #6366f1, #8b5cf6); 
                          color: white; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 15px;'>
                    Buka Dashboard Saya
                </a>
            </div>
        </div>
        <div style='padding: 20px; text-align: center; color: #999; font-size: 13px; background: #f9f9f9;'>
            <p>Email ini dikirim otomatis oleh sistem BeasiswaKu. Jangan balas email ini.</p>
        </div>
    </div>
    ";
}

/**
 * Template email ditolak
 */
function templateEmailDitolak($nama, $namaBeasiswa, $catatan = '') {
    $catatanHtml = $catatan
        ? "<div style='background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 16px; margin: 16px 0;'>
               <strong style='color: #991b1b;'>Catatan dari Admin:</strong>
               <p style='margin: 8px 0 0; color: #555;'>$catatan</p>
           </div>"
        : '';

    return "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9f9f9; border-radius: 12px; overflow: hidden;'>
        <div style='background: linear-gradient(135deg, #ef4444, #dc2626); padding: 30px; text-align: center;'>
            <h1 style='color: white; margin: 0; font-size: 28px;'>📋 Hasil Seleksi</h1>
            <p style='color: rgba(255,255,255,0.9); margin: 8px 0 0;'>Pemberitahuan Pendaftaran Beasiswa</p>
        </div>
        <div style='padding: 30px; background: white;'>
            <p style='font-size: 16px; color: #333;'>Halo, <strong>$nama</strong>!</p>
            <p style='color: #555; line-height: 1.6;'>
                Kami memberitahukan bahwa pendaftaran beasiswa kamu untuk 
                <strong>$namaBeasiswa</strong> belum dapat kami terima pada seleksi kali ini.
            </p>
            $catatanHtml
            <p style='color: #555; line-height: 1.6;'>
                Jangan menyerah! Kamu masih bisa mencoba mendaftar beasiswa lain yang tersedia di sistem kami.
                Terus semangat dan raih impianmu! 💪
            </p>
            <div style='text-align: center; margin: 24px 0;'>
                <a href='http://localhost/project/mahasiswa/daftar_beasiswa.php' 
                   style='display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #6366f1, #8b5cf6); 
                          color: white; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 15px;'>
                    Lihat Beasiswa Lainnya
                </a>
            </div>
        </div>
        <div style='padding: 20px; text-align: center; color: #999; font-size: 13px; background: #f9f9f9;'>
            <p>Email ini dikirim otomatis oleh sistem BeasiswaKu. Jangan balas email ini.</p>
        </div>
    </div>
    ";
}
?>
