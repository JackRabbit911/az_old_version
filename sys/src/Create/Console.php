<?php

namespace Sys\Create;

use Sys\Console\AbstractConsole;

final class Console extends AbstractConsole
{
    public function __invoke($target, $name, $path = 'app')
    {
        $options = [
            'controller' => ['-c', '--ctrl'], 
            'model' => ['-m', '--model'],
            'entity' => ['-e', '--ent'],
            'middleware' => ['-w', '--mw'],
            'validation' => ['-v', '--valid'],
            'interactive' => ['-I', '--intr'],
        ];

        $target = $this->parseArgs('cmevwI', $options, $target);

        if (!is_array($target)) {
            $target = [$target];
        }

        if (in_array('interactive', $target)) {
            $this->interactive($options, $name, $path);
        } else {
            foreach ($target as $func) {
                $this->$func($name, $path);
            }
        }
    }

    public function help()
    {
        $this->climate->out('Usage: cli create
        Print command: create OR --cr OR -c Then
        print target: controller OR --ctrl OR -c then type <name> [path] to create controller <name> in <path> folder
        OR print target: model OR --model OR -m then type <name> [path] to create model <name> in <path> folder,
        OR print target: middleware OR --mw OR -w then type <name> [path] to create middleware <name> in <path> folder,
        OR print target: validation OR --valid OR -v then type <name> [path] to create validationMiddleware <name> in <path> folder,
        OR print target: interactive OR --intr OR -I then type <name> [path] to use interactive mode.
        You can to combine targets in one argument: -cmw (coontroller, model, validationMiddleware)
        Argument [path0 is not required. Default value: app
        Example:
        -c -cmw User
        will be created:
        1) App\Http\Controller\User
        2) App\Model\ModelUser
        3) App\Http\Middleware\UserMiddleware
        ');
    }

    private function interactive($options, $name, $path)
    {
        unset($options['interactive']);
        $options = array_keys($options);
        $options[] = 'Exit';
        $input    = $this->climate->checkboxes('Please send me all of the following:', $options);
        $response = $input->prompt();

        if (in_array('Exit', $options)) {
            return;
        }

        foreach ($response as $target) {
            $this->$target($name, $path);
        }
    }

    private function controller($name, $path)
    {
        $data = [
            'folder' => '/Http/Controller/',
            'blank' => 'sys/src/Create/blanks/controller.php',
        ];
        
        $res = $this->createFile($name, $path, $data);

        if ($res) {
            $this->climate->out("Controller $res was created succsessfully");
        } else {
            $this->climate->to('error')->red("Failed to create controller file");
        }
    }

    private function model($name, $path)
    {
        $data = [
            'folder' => '/Model/',
            'prefix' => 'Model',
        ];

        $res = $this->createFile($name, $path, $data);

        if ($res) {
            $this->climate->out("Model $res was created succsessfully");
        } else {
            $this->climate->to('error')->red("Failed to create model file");
        }
    }

    private function entity($name, $path)
    {
        $name = ucwords($name, '/');

        $data = [
        'folder' => '/Entity/',
        'use' => 'App\Model\Model' . $name,
        'attribute' => 'Model' . $name,
        ];

        $res = $this->createFile($name, $path, $data);

        if ($res) {
            $this->climate->out("Class $res was created succsessfully");
        } else {
            $this->climate->to('error')->red("Failed to create entity file");
        }
    }    

    private function middleware($name, $path)
    {
        $data = [
            'folder' => '/Http/Middleware/',
            'suffix' => 'Middleware',
        ];

        $res = $this->createFile($name, $path, $data);

        if ($res) {
            $this->climate->out("Middleware $res was created succsessfully");
        } else {
            $this->climate->to('error')->red("Failed to create middleware file");
        }
    }

    private function validation($name, $path)
    {
        $data = [
            'folder' => '/Http/Middleware/',
            'suffix' => 'Validation',
        ];

        $res = $this->createFile($name, $path, $data);

        if ($res) {
            $this->climate->out("Middleware $res was created succsessfully");
        } else {
            $this->climate->to('error')->red("Failed to create middleware file");
        }
    }

    private function createFile($name, $path, $data)
    {
        if (!isset($data['blank'])) {
            $blank = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[1]['function'];
            $data['blank'] = 'sys/src/Create/blanks/' . $blank . '.php';
        }
        
        $name = ucwords($name, '/');
        $path = ucwords($path, '/');
        $prefix = $data['prefix'] ?? '';
        $suffix = $data['suffix'] ?? '';

        $file = lcfirst($path) . $data['folder'] . $prefix . $name . $suffix . '.php';

        if (is_file($file)) {
            $this->climate->to('error')->red("File $file already exists");
            return false;
        }

        $prefix = ucfirst($prefix);
        $suffix = ucfirst($suffix);

        $classname = $prefix . basename($name) . $suffix;
        $dir = dirname($file);

        $namespace = str_replace('/', '\\', $path . rtrim($data['folder'] . implode('/', explode('/', $name, -1)), '/'));

        $data['namespace'] = $namespace;
        $data['classname'] = $classname;
        $data['php'] = '<?php' . PHP_EOL;
        
        $str = $this->render($data['blank'], $data);

        if (!is_dir($dir) || !is_writable($dir)) {
            $this->climate->to('error')->red("$dir is not exists or prmission denied");
            return false;
        }

        $res = file_put_contents($file, $str);

        return ($res) ? $namespace . '\\' . $classname : false;
    }

    private function render($file, $data)
    {
        extract($data, EXTR_SKIP);               
        ob_start();
        include $file;
        return ob_get_clean();
    }
}
