<?php

/*
| Documentos legales que el usuario debe aceptar (Ley 1581 de 2012 y Decreto 1377 de 2013).
| Al publicar un cambio sustancial, sube la versión: todos los usuarios deberán aceptarla
| de nuevo antes de seguir usando la app. Mantén estas versiones iguales a las del
| frontend (src/modules/Legal/utils/legalDocuments.js).
*/

return [
    'documents' => [
        'terms' => [
            'title' => 'Términos y Condiciones',
            'version' => '1.0',
        ],

        'privacy' => [
            'title' => 'Política de Tratamiento de Datos Personales',
            'version' => '1.0',
        ],
    ],
];
