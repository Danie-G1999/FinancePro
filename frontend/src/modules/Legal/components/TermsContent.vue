<script setup>
import { LEGAL_DOCUMENTS, RESPONSIBLE } from '../utils/legalDocuments'

const doc = LEGAL_DOCUMENTS.terms
</script>

<template>
    <article class="legal-doc">
        <p class="legal-meta">
            Versión {{ doc.version }} · Vigente desde el {{ doc.effectiveDate }}
        </p>

        <h3>1. Aceptación</h3>
        <p>
            Estos Términos y Condiciones regulan el uso de {{ RESPONSIBLE.appName }}
            (la "Aplicación"), ofrecida por {{ RESPONSIBLE.legalName }},
            identificado con {{ RESPONSIBLE.idNumber }}. Al crear una cuenta o usar la
            Aplicación, el usuario declara que los ha leído y los acepta. La aceptación por
            medios electrónicos tiene plena validez conforme a la Ley 527 de 1999. Si no
            está de acuerdo, no debe usar la Aplicación.
        </p>

        <h3>2. Qué es la Aplicación</h3>
        <p>
            {{ RESPONSIBLE.appName }} es una herramienta para que cada persona registre y
            organice sus finanzas personales: cuentas, ingresos, gastos, deudas, metas de
            ahorro y presupuestos.
        </p>
        <ul>
            <li>
                <strong>No es una entidad financiera</strong> ni está vigilada por la
                Superintendencia Financiera de Colombia. No capta dinero, no realiza pagos
                ni transferencias, y no se conecta a cuentas bancarias.
            </li>
            <li>
                <strong>No presta asesoría financiera, tributaria ni legal.</strong> Los
                cálculos, presupuestos, reportes y recomendaciones son orientativos y se
                basan únicamente en la información que el usuario registra. Las decisiones
                financieras son responsabilidad exclusiva del usuario.
            </li>
        </ul>

        <h3>3. Registro y cuenta</h3>
        <ul>
            <li>Para crear una cuenta el usuario debe ser mayor de 18 años.</li>
            <li>
                El usuario se compromete a suministrar información veraz y a mantenerla
                actualizada.
            </li>
            <li>
                La cuenta es personal e intransferible. El usuario es responsable de la
                confidencialidad de su contraseña y de toda actividad realizada desde su
                cuenta, y debe informar de inmediato cualquier uso no autorizado.
            </li>
            <li>
                Si el usuario ingresa con Google, también aplican los términos de Google
                para ese servicio.
            </li>
        </ul>

        <h3>4. Uso permitido</h3>
        <p>El usuario se obliga a no:</p>
        <ul>
            <li>Usar la Aplicación para fines ilícitos o contrarios a estos Términos.</li>
            <li>
                Intentar acceder a cuentas o información de otros usuarios, o vulnerar la
                seguridad de la Aplicación.
            </li>
            <li>
                Realizar ingeniería inversa, extraer datos de forma automatizada o
                sobrecargar intencionalmente los servidores.
            </li>
            <li>Registrar datos de terceros sin su autorización.</li>
        </ul>

        <h3>5. Información del usuario</h3>
        <p>
            La información financiera registrada pertenece al usuario, quien es el único
            responsable de su exactitud. El tratamiento de los datos personales se rige por
            la <strong>Política de Tratamiento de Datos Personales</strong>, que forma
            parte integral de estos Términos.
        </p>

        <h3>6. Disponibilidad del servicio</h3>
        <p>
            Procuramos que la Aplicación esté disponible de forma continua, pero puede
            presentar interrupciones por mantenimiento, fallas técnicas o causas ajenas a
            nuestro control. Recomendamos al usuario conservar sus propios registros de la
            información que considere importante.
        </p>

        <h3>7. Responsabilidad</h3>
        <p>
            En la medida permitida por la ley colombiana, {{ RESPONSIBLE.legalName }} no
            será responsable por decisiones tomadas con base en la información o los
            cálculos de la Aplicación, por errores en los datos ingresados por el usuario,
            ni por daños derivados del uso indebido de la cuenta por causas atribuibles al
            usuario. Nada de lo anterior limita los derechos que la Ley 1480 de 2011
            (Estatuto del Consumidor) reconoce al usuario.
        </p>

        <h3>8. Propiedad intelectual</h3>
        <p>
            El software, diseño, marcas y contenidos de la Aplicación son propiedad de
            {{ RESPONSIBLE.legalName }} o de sus licenciantes y están protegidos por la
            normativa de derechos de autor y propiedad industrial. Su uso no otorga al
            usuario ningún derecho sobre ellos, más allá de usar la Aplicación conforme a
            estos Términos.
        </p>

        <h3>9. Suspensión y terminación</h3>
        <p>
            El usuario puede dejar de usar la Aplicación y solicitar la eliminación de su
            cuenta en cualquier momento escribiendo a {{ RESPONSIBLE.email }}. Podremos
            suspender o cancelar cuentas que incumplan estos Términos, previa notificación
            cuando sea posible.
        </p>

        <h3>10. Modificaciones</h3>
        <p>
            Podemos actualizar estos Términos. Los cambios sustanciales se informarán dentro
            de la Aplicación y será necesario aceptarlos para continuar usándola. La versión
            y fecha de vigencia aparecen al inicio de este documento.
        </p>

        <h3>11. Ley aplicable y contacto</h3>
        <p>
            Estos Términos se rigen por las leyes de la República de Colombia. Cualquier
            consulta puede dirigirse a {{ RESPONSIBLE.email }} o al teléfono
            {{ RESPONSIBLE.phone }}.
        </p>
    </article>
</template>

<style scoped src="../styles/legal-doc.css"></style>
