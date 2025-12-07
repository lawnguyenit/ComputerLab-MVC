<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NewUserQrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $qrCodeImage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
        
        // Tạo nội dung QR code
        $qrContent = json_encode([
            'username' => $user->ten_tai_khoan,
            'password' => $password,
            'email' => $user->email,
            'app' => 'QLComputerLab'
        ]);
        
        // Tạo QR code dạng base64 để nhúng vào email
        $this->qrCodeImage = base64_encode(QrCode::format('png')
                                ->size(300)
                                ->errorCorrection('H')
                                ->generate($qrContent));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Thông tin đăng nhập - Mã QR Code')
                    ->view('emails.new-user-qrcode');
    }
}