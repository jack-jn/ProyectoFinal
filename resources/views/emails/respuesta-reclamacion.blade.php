<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Respuesta a Libro de Reclamaciones | Academia de Música Dunamys</title>
</head>
<body style="margin:0;padding:0;background-color:#F4F2F3;font-family:'Figtree',Arial,Helvetica,sans-serif;color:#191718;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F4F2F3;padding:28px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#FFFFFF;border-radius:14px;overflow:hidden;border:1px solid #E7E3E4;">
          <tr>
            <td style="background-color:#191718;padding:22px 28px;border-bottom:4px solid #CE2127;">
              <span style="font-size:20px;font-weight:700;color:#FFFFFF;letter-spacing:-0.02em;">
                Academia de Música <span style="color:#CE2127;">Dunamys</span>
              </span>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;font-size:15px;line-height:1.6;color:#2B2729;">
              <p style="margin:0 0 12px 0;font-size:16px;font-weight:700;color:#191718;">
                Hola, {{ $reclamacion->nombres }} {{ $reclamacion->apellidos }}:
              </p>
              <p style="margin:0 0 16px 0;">
                Hemos atendido tu <strong>{{ $reclamacion->tipo }}</strong> registrado en nuestro Libro de Reclamaciones con el código <strong style="color:#CE2127;">{{ $reclamacion->codigo }}</strong>.
              </p>
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#FAF9F9;border:1px solid #E7E3E4;border-left:4px solid #CE2127;border-radius:8px;margin-bottom:20px;">
                <tr>
                  <td style="padding:16px 18px;font-size:14.5px;line-height:1.6;color:#191718;">
                    <strong style="display:block;margin-bottom:6px;color:#CE2127;">Respuesta de la academia:</strong>
                    {{ $reclamacion->respuesta }}
                  </td>
                </tr>
              </table>
              <p style="margin:0;font-size:13.5px;color:#6B6568;">
                Fecha de atención: {{ $reclamacion->fecha_atencion?->format('d/m/Y H:i') }}
              </p>
            </td>
          </tr>
          <tr>
            <td style="background-color:#FAF9F9;padding:18px 28px;border-top:1px solid #E7E3E4;text-align:center;font-size:13px;color:#6B6568;">
              <p style="margin:0;font-weight:700;color:#191718;">Academia de Música Dunamys — La música transforma vidas.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
