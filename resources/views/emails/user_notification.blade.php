<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Undangan Uji Kemampuan Teknis - AMDALNET</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f4f4" style="padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #e0e0e0;padding:20px;font-family:Arial,sans-serif;">

                    {{-- Logo + Header --}}
                    <tr>
                        <td style="padding: 10px 0 20px 0; border-bottom: 1px solid #ccc;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="table-layout: fixed;">
                                <tr>
                                    {{-- Logo --}}
                                    <td style="width: 100px; text-align: center; vertical-align: middle;">
                                        @php
                                            $isEmail = isset($message);
                                            $logoPath = $isEmail
                                                ? $message->embed(public_path('logo-klh.png'))
                                                : asset('logo-klh.png');
                                        @endphp
                                        <img src="{{ $logoPath }}" alt="Logo KLHK" style="width:100px; max-width:100%;">
                                    </td>

                                    {{-- Header Text --}}
                                    <td style="text-align: center; vertical-align: middle;">
                                        <div style="line-height: 1.5;">
                                            <h1 style="font-size:16px; margin:0; font-weight:bold;">KEMENTERIAN LINGKUNGAN HIDUP /</h1>
                                            <h1 style="font-size:16px; margin:0; font-weight:bold;">BADAN PENGENDALIAN LINGKUNGAN HIDUP</h1>
                                            <h2 style="font-size:16px; margin:5px 0; font-weight:bold;">
                                                DIREKTORAT PENCEGAHAN DAMPAK LINGKUNGAN USAHA DAN KEGIATAN
                                            </h2>
                                            <p style="font-size:12px; margin:0;">
                                                Blok IV Lt. 6, Wing C, Gedung Manggala Wanabakti, Jl. Jend. Gatot Subroto, Jakarta 10270
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>


                    {{-- Isi Email --}}
                    <tr>
                        <td style="padding: 20px 10px 10px 10px; font-size: 14px; line-height: 1.7; text-align: justify;">
                            <p>Halo <strong>{{ $name }}</strong>,</p>

                            <p>
                                Selamat! Anda dinyatakan <strong>LULUS TAHAP SELEKSI ADMINISTRASI</strong> dalam proses pengadaan Calon Tenaga Pendukung Teknis Dokumen Lingkungan Hidup – AMDALNET.
                            </p>

                            <p>
                                Dengan hasil ini, Anda berhak melanjutkan ke tahap selanjutnya, yaitu <strong>UJI KEMAMPUAN TEKNIS</strong> yang akan dilaksanakan pada hari senin 30 Juni 2025.
                            </p>

                            <p>
                                Informasi lebih lanjut mengenai pelaksanaan Tes Tertulis Online akan diumumkan pada portal berita terkini secara berkala melalui laman
                                <a href="https://amdalnet.menlhk.go.id" target="_blank" style="color:#1a73e8; text-decoration:none;">amdalnet.menlhk.go.id</a>
                            </p>

                            <p style="margin-top: 30px;">
                                Terima kasih,<br>
                                <strong>Panitia Seleksi AMDALNET</strong>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding: 20px 10px 0 10px;border-top:1px solid #ccc;">
                            <p style="font-size:11px;color:#888;margin:0;">
                                Email ini dikirim otomatis oleh sistem. Jangan balas email ini.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
