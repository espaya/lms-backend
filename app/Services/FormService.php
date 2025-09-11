<?php 

namespace App\Services;

class FormService
{
    /* check if applicant has filled the form and display them in the menu dropdown list items*/

    public static function isAnyFormFilled($formModelClassNames, $applicant_id)
    {
        foreach($formModelClassNames as $formModelClassName)
        { 
            $formModelClass = app($formModelClassName);

            $form = $formModelClass::where('applicant_id', $applicant_id)->first();

            if($form)
            {
                return $form->id;
            }
        } 
        
        return null;
    }
}