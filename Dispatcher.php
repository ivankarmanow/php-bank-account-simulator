<?php

class Dispatcher
{
    private array $paths;
    private array $event_handlers;
    private array $exception_handlers;

    public function __construct(private FiniteStateMachine $fsm, private string $exit_command = "exit", private string $separator = " ", private string $inv = "-> ")
    {
        $this->paths = array();
        $this->event_handlers = array();
        $this->exception_handlers = array();
    }

    public function register(string $command, callable $callback, States|null $state = null): void
    {
        if (!is_callable($callback)) {
            throw new exceptions\CallbackIsNotCallbable();
        }
        $reflector = new ReflectionFunction($callback);
        $callback_params = $reflector->getParameters();
        $params = array();
        foreach ($callback_params as $prm) {
            if ($prm->getType() != Command::class and $prm->getType() != FiniteStateMachine::class) {
                $params[] = $prm->getName();
            }
        }
//        if ($this->has_param_type($callback, Command::class)) {
//            $params--;
//        }
//        if ($this->has_param_type($callback, FiniteStateMachine::class)) {
//            $params--;
//        }
        $this->paths[] = [
            "command" => $command,
            "params" => $params,
            "state" => $state,
            "callback" => $callback
        ];
    }

    public function event_handler(string $event, callable $handler): void
    {
        $this->event_handlers[$event] = $handler;
    }

    public function exception(string $exception, callable $handler)
    {
        $this->exception_handlers[$exception] = $handler;
    }

    public function has_param(callable $callback, string $param_name): bool
    {
        $reflector = new ReflectionFunction($callback);
        $params = $reflector->getParameters();
        foreach ($params as $param) {
            if ($param->name == $param_name) {
                return true;
            }
        }
        return false;
    }

    public function has_param_type(callable $callback, string $param_type): string | null
    {
        $reflector = new ReflectionFunction($callback);
        $params = $reflector->getParameters();
        foreach ($params as $param) {
            if ($param->getType()->getName() == $param_type) {
                return $param->getName();
            }
        }
        return null;
    }

    private function resolve(string $prompt): string
    {
        $parts = explode($this->separator, $prompt);
        [$command, $params] = [$parts[0], array_slice($parts, 1)];
        foreach ($this->paths as ["command" => $cmd, "params" => $prms, "state" => $state, "callback" => $callback]) {
            if (($command == $cmd or $cmd == "*") and count($params) == count($prms) and $this->fsm->state == $state) {
//                var_dump($params);
                $data = array();
                $c = 0;
                foreach ($prms as $prm) {
                    $data[$prm] = $params[$c];
                    $c++;
                }
                $command_param = $this->has_param_type($callback, Command::class);
                if ($command_param) {
                    $data[$command_param] = new Command($prompt, $command, $params);
                }
                $fsm_param = $this->has_param_type($callback, FiniteStateMachine::class);
                if ($fsm_param) {
                    $data[$fsm_param] = $this->fsm;
                }
                return $callback(...$data) . PHP_EOL;
            }
        }
        throw new exceptions\CallbackNotFound();
    }

    public function event(string $event): void
    {
        $this->event_handlers[$event]($this->fsm);
    }

    public function run(): void
    {
        $this->event("startup");
        while (true) {
            $prompt = readline($this->inv);
            if ($prompt == $this->exit_command) {
                break;
            }
            try {
                echo $this->resolve($prompt);
            } catch (Exception $e) {
                $this->exception_handlers[$e::class]($e);
            }
        }
        $this->event("shutdown");
    }
}