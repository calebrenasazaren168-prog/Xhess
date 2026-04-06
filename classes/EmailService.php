<?php
/**
 * EmailService.php
 * Ubicación: classes/EmailService.php
 *
 * Envía correos usando la API HTTP de Brevo.
 * No requiere PHPMailer ni configuración SMTP.
 */

class EmailService {

    // ── Configuración ──────────────────────────────────────────────────────
    private const API_KEY  = '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';   // ← CAMBIA ESTO (xkeysib-...)
    private const URL_BASE = 'https://xhess.gt.tc';    // ← tu dominio (ya correcto)
    // ──────────────────────────────────────────────────────────────────────

    private const API_URL   = 'https://api.brevo.com/v3/smtp/email';
    private const FROM_NAME  = 'XHESS';
    private const FROM_EMAIL = 'xhess.oficial@gmail.com';

    /**
     * Envía el correo de verificación al usuario recién registrado.
     *
     * @param string $correo   Email del destinatario
     * @param string $usuario  Nombre de usuario
     * @param string $token    Token de verificación de 64 caracteres
     */
    public function enviarVerificacion(string $correo, string $usuario, string $token): void {
        $enlace = self::URL_BASE . '/verificar_email.php?token=' . urlencode($token);

        $datos = [
            'sender' => [
                'name'  => self::FROM_NAME,
                'email' => self::FROM_EMAIL,
            ],
            'to' => [
                ['email' => $correo, 'name' => $usuario]
            ],
            'subject'     => 'XHESS – Verifica tu cuenta',
            'htmlContent' => $this->plantillaVerificacion($usuario, $enlace),
            'textContent' => "Hola {$usuario},\n\nVerifica tu cuenta en: {$enlace}\n\nEl enlace expira en 24 horas.\n\n© 2025 XHESS",
        ];

        $this->enviar($datos);
    }

    // ── Métodos privados ───────────────────────────────────────────────────

    /**
     * Realiza la petición HTTP POST a la API de Brevo.
     * Usa cURL si está disponible, si no usa file_get_contents.
     */
    private function enviar(array $datos): void {
        $json    = json_encode($datos);
        $headers = [
            'accept: application/json',
            'api-key: ' . self::API_KEY,
            'content-type: application/json',
        ];

        // Intentar con cURL primero
        if (function_exists('curl_init')) {
            $ch = curl_init(self::API_URL);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $json,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $respuesta  = curl_exec($ch);
            $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error) {
                error_log('[XHESS EmailService] cURL error: ' . $curl_error);
                throw new RuntimeException('No se pudo conectar con el servicio de correo.');
            }

        } else {
            // Fallback: file_get_contents
            $contexto = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => implode("\r\n", $headers),
                    'content' => $json,
                    'timeout' => 15,
                ],
            ]);

            $respuesta = @file_get_contents(self::API_URL, false, $contexto);
            $http_code = 0;

            if (isset($http_response_header)) {
                preg_match('/HTTP\/\d\.\d (\d+)/', $http_response_header[0], $m);
                $http_code = isset($m[1]) ? (int)$m[1] : 0;
            }

            if ($respuesta === false) {
                error_log('[XHESS EmailService] file_get_contents falló al llamar a la API de Brevo.');
                throw new RuntimeException('No se pudo conectar con el servicio de correo.');
            }
        }

        // Verificar respuesta de Brevo (201 = éxito)
        if ($http_code !== 201) {
            error_log('[XHESS EmailService] Brevo respondió HTTP ' . $http_code . ': ' . $respuesta);
            throw new RuntimeException('El servicio de correo rechazó el envío. Intenta más tarde.');
        }
    }

    /**
     * Genera el HTML del correo de verificación.
     */
    private function plantillaVerificacion(string $usuario, string $enlace): string {
        $usuario_safe = htmlspecialchars($usuario);
        $enlace_safe  = htmlspecialchars($enlace);

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <body style='margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:40px 0;'>
                <tr>
                    <td align='center'>
                        <table width='520' cellpadding='0' cellspacing='0'
                               style='background:#ffffff;border-radius:8px;
                                      border:1px solid #ddd;overflow:hidden;'>

                            <!-- Encabezado -->
                            <tr>
                                <td style='background:linear-gradient(135deg,#77e9f8,#56ce46);
                                           padding:30px;text-align:center;'>
                                    <h1 style='color:#fff;margin:0;font-size:28px;letter-spacing:2px;'>
                                        ♟ XHESS
                                    </h1>
                                    <p style='color:#f0fff0;margin:6px 0 0;font-size:13px;'>
                                        Sistema de Torneos de Ajedrez
                                    </p>
                                </td>
                            </tr>

                            <!-- Cuerpo -->
                            <tr>
                                <td style='padding:36px 40px;'>
                                    <h2 style='color:#2c3e50;margin:0 0 16px;font-size:20px;'>
                                        ¡Bienvenido, {$usuario_safe}!
                                    </h2>
                                    <p style='color:#555;line-height:1.6;margin:0 0 24px;'>
                                        Gracias por registrarte en XHESS. Para activar tu cuenta
                                        y poder iniciar sesión, haz clic en el botón de abajo:
                                    </p>

                                    <!-- Botón -->
                                    <table width='100%' cellpadding='0' cellspacing='0'>
                                        <tr>
                                            <td align='center' style='padding:10px 0 28px;'>
                                                <a href='{$enlace_safe}'
                                                   style='background:#007bff;color:#ffffff;
                                                          padding:14px 36px;border-radius:6px;
                                                          text-decoration:none;font-size:16px;
                                                          font-weight:bold;display:inline-block;'>
                                                    ✅ Verificar mi cuenta
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style='color:#888;font-size:13px;line-height:1.6;margin:0 0 8px;'>
                                        Si el botón no funciona, copia este enlace en tu navegador:
                                    </p>
                                    <p style='word-break:break-all;margin:0 0 24px;'>
                                        <a href='{$enlace_safe}' style='color:#007bff;font-size:12px;'>
                                            {$enlace_safe}
                                        </a>
                                    </p>

                                    <div style='background:#fff8e1;border-left:4px solid #ffc107;
                                                padding:12px 16px;border-radius:4px;'>
                                        <p style='margin:0;color:#856404;font-size:13px;'>
                                            ⏳ Este enlace expira en <strong>24 horas</strong>.
                                        </p>
                                    </div>
                                </td>
                            </tr>

                            <!-- Pie -->
                            <tr>
                                <td style='background:#f8f9fa;padding:20px 40px;
                                           border-top:1px solid #eee;text-align:center;'>
                                    <p style='color:#aaa;font-size:11px;margin:0;'>
                                        Si no creaste esta cuenta en XHESS, ignora este mensaje.<br>
                                        © 2025 XHESS · xhess.oficial@gmail.com
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }
}