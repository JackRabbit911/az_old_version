<?=$php?>

namespace <?=$namespace?>;

use Az\Validation\ValidationMiddleware;

final class <?=$classname?> extends ValidationMiddleware
{
    protected function setRules()
    {
        $this->validation->rule();
    }
}
