<?php

namespace App\Util;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of ValidatorUtil
 *
 * @author zaealan
 */
class ValidatorUtil {

    /**
     * Esta funcion permite validar de forma dinamica los valores 
     * ingresados segun el criterio de validacion señalado dentro 
     * de los mismo argumentos de la funcion
     * @author Aealan Z <lrobledo@kijho.com> 12/05/2016
     * @param string $theValidator like the Judge Dreed for validations ;)
     * @param string $typeValueArray array with the mixed value to validate, data and extra info required to validate
     * @return string contraseña codificada
     */
    public static function validateThis($theValidator, $typeValueArray) {

        $responseValidationArray = [];
        $isAllValid = true;

        foreach ($typeValueArray as $key => $value) {
            if ($value['type'] == 'regex') {
                $regexConstraint = new Assert\Regex(['pattern' => $value['pattern'], 'message' => $value['message']]);
            }

            $responseValidationArray[$key]['isValid'] = false;
            $responseValidationArray[$key]['field'] = $value['field'];

            if ($theValidator && $regexConstraint) {
                $validationResult = $theValidator->validate($value['data'], $regexConstraint);
            } else {
                $responseValidationArray[$key]['message'] = 'Unconsistent constraint... did you miss some extra constraint parameter?';
                $responseValidationArray[$key]['data'] = $value['data'];
                $isAllValid = false;
                continue;
            }

            if (0 != count($validationResult)) {
                $responseValidationArray[$key]['message'] = $validationResult[0]->getMessage();
                $responseValidationArray[$key]['data'] = $value['data'];
                $isAllValid = false;
            } else {
                $responseValidationArray[$key]['message'] = 'Valid!';
                $responseValidationArray[$key]['isValid'] = true;
                $responseValidationArray[$key]['data'] = $value['data'];
            }
        }

        return [$isAllValid, $responseValidationArray];
    }

}
