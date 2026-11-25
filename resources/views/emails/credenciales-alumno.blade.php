<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academia de Música Dunamys</title>
</head>
<body style="margin:0;padding:0;background-color:#F4F2F3;font-family:'Figtree',Arial,Helvetica,sans-serif;color:#191718;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F4F2F3;padding:28px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#FFFFFF;border-radius:14px;overflow:hidden;border:1px solid #E7E3E4;">
          <!-- Cabecera -->
          <tr>
            <td style="background-color:#191718;padding:22px 28px;border-bottom:4px solid #CE2127;">
              <span style="font-size:20px;font-weight:700;color:#FFFFFF;letter-spacing:-0.02em;">
                Academia de Música <span style="color:#CE2127;">Dunamys</span>
              </span>
            </td>
          </tr>

          <!-- Cuerpo -->
          <tr>
            <td style="padding:30px 28px 24px 28px;font-size:15px;line-height:1.6;color:#2B2729;">
              <p style="margin:0 0 14px 0;font-size:16px;font-weight:700;color:#191718;">
                Hola, {{ $nombreCompleto }}:
              </p>

              <p style="margin:0 0 18px 0;">
                ¡Te damos la bienvenida! Tu pago ha sido validado correctamente y tu matrícula en modalidad 100% Virtual ya se encuentra activa.
              </p>

              <!-- Resumen de Matrícula -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#FAF9F9;border:1px solid #E7E3E4;border-radius:10px;margin-bottom:20px;">
                <tr>
                  <td style="padding:14px 18px;font-size:14px;line-height:1.5;">
                    <div style="margin-bottom:6px;">
                      <strong style="color:#191718;">Código de matrícula:</strong>
                      <span style="color:#CE2127;font-weight:700;">{{ $codigoMatricula }}</span>
                    </div>
                    <div>
                      <strong style="color:#191718;">Curso(s) habilitado(s):</strong>
                      <span>{{ implode(', ', $cursosHabilitados) }}</span>
                    </div>
                  </td>
                </tr>
              </table>

              <!-- Bloque de Credenciales -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#191718;border-radius:12px;margin-bottom:22px;">
                <tr>
                  <td style="padding:20px 22px;color:#FFFFFF;font-size:14.5px;line-height:1.6;">
                    @if ($esCuentaNueva)
                      <p style="margin:0 0 12px 0;font-weight:700;color:#FFFFFF;">
                        Estas son tus credenciales de acceso al Aula Virtual:
                      </p>
                      <p style="margin:0 0 6px 0;color:#E7E3E4;">
                        <strong style="color:#FFFFFF;">Correo de acceso:</strong> {{ $correoAcceso }}
                      </p>
                      <p style="margin:0 0 14px 0;color:#E7E3E4;">
                        <strong style="color:#FFFFFF;">Contraseña temporal:</strong>
                        <span style="display:inline-block;background-color:#2B2729;border:1px solid rgba(255,255,255,0.2);padding:3px 10px;border-radius:6px;font-family:monospace;font-size:15px;font-weight:700;color:#FFFFFF;letter-spacing:0.05em;">{{ $passwordTemporal }}</span>
                      </p>
                      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#FEF3DC;border-left:4px solid #D9900D;border-radius:6px;">
                        <tr>
                          <td style="padding:10px 14px;color:#855400;font-size:13px;line-height:1.5;">
                            Recuerda que esta contraseña es temporal. Por tu seguridad, una vez que ingreses a la plataforma deberás cambiarla por una contraseña personal a tu gusto.
                          </td>
                        </tr>
                      </table>
                    @else
                      <p style="margin:0;color:#E7E3E4;">
                        Tu nuevo curso ya se encuentra disponible en tu Aula Virtual. Puedes ingresar usando tu correo (<strong style="color:#FFFFFF;">{{ $correoAcceso }}</strong>) y tu contraseña habitual.
                      </p>
                    @endif
                  </td>
                </tr>
              </table>

              <!-- Botón principal -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 22px auto;">
                <tr>
                  <td align="center" style="background-color:#CE2127;border-radius:10px;">
                    <a href="{{ route('login') }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:700;color:#FFFFFF;text-decoration:none;">
                      Ingresar al Aula Virtual
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Pie -->
          <tr>
            <td style="background-color:#FAF9F9;padding:18px 28px;border-top:1px solid #E7E3E4;text-align:center;font-size:13px;line-height:1.5;color:#6B6568;">
              <p style="margin:0 0 4px 0;">Si tienes alguna consulta, comunícate con secretaría.</p>
              <p style="margin:0;font-weight:700;color:#191718;">Academia de Música Dunamys — La música transforma vidas.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
