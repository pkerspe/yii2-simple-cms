<?php
namespace schallschlucker\simplecms\behaviours;

use yii\behaviors\BlameableBehavior;

class CmsBlameableBehavior extends BlameableBehavior
{
    /**
     * Evaluates the value of the user.
     * The return result of this method will be assigned to the current attribute(s).
     * @param Event $event
     * @return mixed the value of the user.
     */
    protected function getValue($event){
        $value = parent::getValue($event);
        if( $value == null ){
            return 1;
        }
        return $value;
    }
    
}