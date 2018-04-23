<?php

namespace Bloomkit\Core\Entities\Fields;

/**
 * Definition of the different field types.
 */
abstract class FieldType
{
    const PDynFTCustom = 'PDynFTCustom';

    const PDynFTBoolean = 'PDynFTBoolean';

    const PDynFTDate = 'PDynFTDate';

    const PDynFTDuration = 'PDynFTDuration';

    const PDynFTTimestamp = 'PDynFTTimestamp';

    const PDynFTTime = 'PDynFTTime';

    const PDynFTString = 'PDynFTString';

    const PDynFTInteger = 'PDynFTInteger';

    const PDynFTDecimal = 'PDynFTDecimal';

    const PDynFTCurrency = 'PDynFTCurrency';

    const PDynFTPercent = 'PDynFTPercent';

    const PDynFTPassword = 'PDynFTPassword';

    const PDynFTReference = 'PDynFTReference';

    const PDynFTMemo = 'PDynFTMemo';

    const PDynFTEnum = 'PDynFTEnum';
}
