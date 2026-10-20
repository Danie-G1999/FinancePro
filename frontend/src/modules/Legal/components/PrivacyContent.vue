<script setup>
import { LEGAL_DOCUMENTS, RESPONSIBLE } from '../utils/legalDocuments'

const doc = LEGAL_DOCUMENTS.privacy
</script>

<template>
    <article class="legal-doc">
        <p class="legal-meta">
            Versión {{ doc.version }} · Vigente desde el {{ doc.effectiveDate }}
        </p>

        <p>
            En cumplimiento de la Ley Estatutaria 1581 de 2012, el Decreto 1377 de 2013
            (compilado en el Decreto Único Reglamentario 1074 de 2015) y demás normas que
            los modifiquen o complementen, {{ RESPONSIBLE.appName }} adopta la presente
            Política para el tratamiento de los datos personales de sus usuarios.
        </p>

        <h3>1. Responsable del tratamiento</h3>
        <ul class="legal-data">
            <li><strong>Responsable:</strong> {{ RESPONSIBLE.legalName }}</li>
            <li><strong>Identificación:</strong> {{ RESPONSIBLE.idNumber }}</li>
            <li><strong>Domicilio:</strong> {{ RESPONSIBLE.address }}, {{ RESPONSIBLE.city }}</li>
            <li><strong>Correo electrónico:</strong> {{ RESPONSIBLE.email }}</li>
            <li><strong>Teléfono:</strong> {{ RESPONSIBLE.phone }}</li>
        </ul>

        <h3>2. Definiciones</h3>
        <p>
            Para efectos de esta Política se aplican las definiciones del artículo 3 de la
            Ley 1581 de 2012. En especial: <strong>Titular</strong> es la persona natural
            cuyos datos son objeto de tratamiento; <strong>Tratamiento</strong> es cualquier
            operación sobre datos personales, como la recolección, almacenamiento, uso,
            circulación o supresión; <strong>Autorización</strong> es el consentimiento
            previo, expreso e informado del Titular; y <strong>Encargado</strong> es quien
            realiza el tratamiento por cuenta del Responsable.
        </p>

        <h3>3. Datos que recolectamos</h3>
        <ul>
            <li>
                <strong>De identificación y contacto:</strong> nombre, correo electrónico,
                teléfono, fecha de nacimiento, dirección y foto de perfil.
            </li>
            <li>
                <strong>Socioeconómicos:</strong> tipo de ocupación, tipo de contrato,
                aportes a seguridad social e ingreso mensual, cuando el usuario los registra
                en su perfil.
            </li>
            <li>
                <strong>Financieros registrados por el usuario:</strong> cuentas y saldos,
                ingresos, gastos, categorías, deudas, metas de ahorro y presupuestos. Esta
                información la ingresa el propio usuario; {{ RESPONSIBLE.appName }} no se
                conecta a bancos ni a otras entidades para obtenerla.
            </li>
            <li>
                <strong>De autenticación:</strong> contraseña (almacenada cifrada, nunca en
                texto plano) y, si el usuario elige ingresar con Google, el identificador de
                su cuenta de Google, su nombre y su correo.
            </li>
            <li>
                <strong>Técnicos:</strong> dirección IP, tipo de navegador y cookies de
                sesión necesarias para mantener la sesión iniciada y proteger la cuenta.
            </li>
            <li>
                <strong>De terceros:</strong> el nombre, parentesco y teléfono de la
                referencia familiar que el usuario registre de forma opcional. Al
                suministrarlos, el usuario declara que cuenta con la autorización de esa
                persona para hacerlo.
            </li>
        </ul>
        <p>
            {{ RESPONSIBLE.appName }} no solicita datos sensibles (origen racial o étnico,
            orientación política, convicciones religiosas, datos de salud, vida sexual o
            datos biométricos). El usuario no está obligado a suministrarlos y le pedimos
            no registrarlos en campos libres como notas o descripciones.
        </p>

        <h3>4. Finalidades del tratamiento</h3>
        <p>Los datos se tratan exclusivamente para:</p>
        <ol>
            <li>Crear y administrar la cuenta del usuario y permitir su autenticación.</li>
            <li>
                Prestar el servicio: registrar y organizar la información financiera del
                usuario, calcular saldos, presupuestos (regla 50/30/20), reportes,
                estadísticas y recomendaciones personalizadas.
            </li>
            <li>Garantizar la seguridad de la cuenta y prevenir accesos no autorizados o fraudes.</li>
            <li>
                Atender consultas, reclamos y solicitudes del Titular, y enviar
                comunicaciones relacionadas con el servicio (cambios en estos documentos,
                avisos de seguridad).
            </li>
            <li>
                Elaborar estadísticas internas de uso de forma agregada y anonimizada para
                mejorar la aplicación.
            </li>
            <li>Cumplir obligaciones legales y requerimientos de autoridades competentes.</li>
        </ol>
        <p>
            {{ RESPONSIBLE.appName }} <strong>no vende, alquila ni comercializa</strong> los
            datos personales, ni los usa para publicidad de terceros.
        </p>

        <h3>5. Circulación, encargados y transferencias</h3>
        <p>
            Los datos de cada usuario solo son visibles para ese usuario dentro de la
            aplicación. Podrán ser tratados por encargados que nos prestan servicios
            tecnológicos (por ejemplo, alojamiento de servidores o el servicio de inicio de
            sesión de Google), bajo contratos que les obligan a proteger la información y a
            usarla solo para prestarnos ese servicio. Cuando estos proveedores se
            encuentren fuera de Colombia, la transmisión se hará conforme a los artículos
            25 y 26 de la Ley 1581 de 2012 y las instrucciones de la Superintendencia de
            Industria y Comercio. Con la autorización de esta Política, el Titular autoriza
            de manera expresa dichas transmisiones.
        </p>

        <h3>6. Derechos del Titular</h3>
        <p>De acuerdo con el artículo 8 de la Ley 1581 de 2012, el Titular tiene derecho a:</p>
        <ol type="a">
            <li>Conocer, actualizar y rectificar sus datos personales.</li>
            <li>Solicitar prueba de la autorización otorgada.</li>
            <li>Ser informado, previa solicitud, sobre el uso que se ha dado a sus datos.</li>
            <li>
                Presentar quejas ante la Superintendencia de Industria y Comercio por
                infracciones a la ley, una vez agotado el trámite de consulta o reclamo
                ante el Responsable.
            </li>
            <li>
                Revocar la autorización y/o solicitar la supresión de sus datos cuando no
                exista un deber legal o contractual de conservarlos.
            </li>
            <li>Acceder en forma gratuita a sus datos personales objeto de tratamiento.</li>
        </ol>
        <p>
            Muchos de estos derechos pueden ejercerse directamente en la aplicación: el
            usuario puede consultar y actualizar su perfil y editar o eliminar su
            información financiera en cualquier momento.
        </p>

        <h3>7. Área responsable y procedimiento</h3>
        <p>
            Las consultas, reclamos y solicitudes se atienden a través del correo
            <strong>{{ RESPONSIBLE.email }}</strong>. La solicitud debe incluir el nombre e
            identificación del Titular, la descripción de lo que solicita, el correo de la
            cuenta y un medio para la respuesta.
        </p>
        <ul>
            <li>
                <strong>Consultas</strong> (art. 14, Ley 1581 de 2012): se responden en un
                máximo de diez (10) días hábiles desde su recibo. Si no es posible, se
                informará el motivo y la nueva fecha, que no superará cinco (5) días hábiles
                adicionales.
            </li>
            <li>
                <strong>Reclamos</strong> (art. 15, Ley 1581 de 2012) para corrección,
                actualización, supresión o por presunto incumplimiento: se responden en un
                máximo de quince (15) días hábiles desde el día siguiente a su recibo,
                prorrogables hasta ocho (8) días hábiles más, informando el motivo. Si el
                reclamo está incompleto, se pedirá completarlo dentro de los cinco (5) días
                siguientes; pasados dos (2) meses sin respuesta del Titular se entenderá
                desistido.
            </li>
        </ul>

        <h3>8. Seguridad de la información</h3>
        <p>
            Aplicamos medidas técnicas, humanas y administrativas razonables para proteger
            los datos contra adulteración, pérdida, consulta, uso o acceso no autorizado:
            contraseñas cifradas, separación estricta de la información de cada usuario,
            límites de intentos de inicio de sesión y acceso restringido a los servidores.
            Ningún sistema es completamente infalible; si ocurriera un incidente de
            seguridad, se informará a los Titulares afectados y a la Superintendencia de
            Industria y Comercio conforme a la ley.
        </p>

        <h3>9. Menores de edad</h3>
        <p>
            El servicio está dirigido a mayores de 18 años. No recolectamos
            intencionalmente datos de niños, niñas y adolescentes.
        </p>

        <h3>10. Conservación</h3>
        <p>
            Los datos se conservan mientras la cuenta esté activa y durante el tiempo
            necesario para cumplir las finalidades descritas o los deberes legales. Si el
            Titular solicita la supresión, los datos se eliminarán salvo aquellos que deban
            conservarse por obligación legal, incluida la prueba de la autorización.
        </p>

        <h3>11. Vigencia y cambios</h3>
        <p>
            Esta Política rige desde el {{ doc.effectiveDate }}. Los cambios sustanciales
            se informarán dentro de la aplicación y, cuando afecten las finalidades, se
            solicitará una nueva autorización antes de continuar con el tratamiento.
        </p>

        <h3>12. Autorización</h3>
        <p>
            Al marcar la casilla correspondiente, el Titular declara que ha leído esta
            Política y otorga su autorización previa, expresa e informada para que
            {{ RESPONSIBLE.legalName }} trate sus datos personales conforme a lo aquí
            descrito. La aceptación queda registrada con fecha, versión del documento y
            datos técnicos de la conexión, como prueba de la autorización (art. 8, Decreto
            1377 de 2013).
        </p>
    </article>
</template>

<style scoped src="../styles/legal-doc.css"></style>
