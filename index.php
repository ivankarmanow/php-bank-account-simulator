<?php
spl_autoload_register(function ($class) {
    $file = __DIR__ . '\\' . $class . '.php';

    if (file_exists($file)) {
        require $file;
    } else {
        echo $file;
    }
});

function balance(FiniteStateMachine $fsm): string
{
    $account = $fsm['account'];
    return $account->amount();
}

function in(FiniteStateMachine $fsm, int $amount): string
{
    $account = $fsm['account'];
    $account->in($amount);
    return "Счёт пополнен на $amount";
}

function out(FiniteStateMachine $fsm, int $amount): string
{
    $account = $fsm['account'];
    $account->out($amount);
    return "Вы сняли $amount рублей";
}

function nomoney(Exception $e): void
{
    echo "На вашем счёте недостаточно денег!" . PHP_EOL;
}

function cooldawn(\exceptions\WithdrawalBlocked $e): void
{
    $s = $e->seconds;
    echo "Вы недавно пополнили счёт. Снятие средств станет доступно через $s секунд." . PHP_EOL;
}

function command404(Exception $e): void
{
    echo "Такой комманды не существуте!" . PHP_EOL;
}

function start(FiniteStateMachine $fsm): void
{
    echo "Выберите тип счёта:\n";
    $c = 1;
    foreach ($fsm['factory']->iter() as $resource => $class) {
        echo "$c - $resource\n";
        $c++;
    }
    $fsm->state = States::SelectAccountType;
}

function select_account_type(FiniteStateMachine $fsm, Command $command): string
{
    try {
        $account = $fsm['factory']->build((int)$command->prompt);
        $fsm['account'] = $account;
        $fsm->state = States::OnlineLoop;
        return 'Ваш счёт успешно создан!
Чтобы пополнить счёт, ведите команду in <amount>, где <amount> - сумма пополнения
Чтобы вывести деньги, введите команду out <amount>, где <amount> - сумма пополнения
Чтобы получить баланс счёта, введите balance
Чтобы выйти из программы, введи exit';
    } catch (\exceptions\ResourceNotFound $e) {
        return "Вы ввели неверное число! Попробуйте снова";
    }
}

function shutdown(FiniteStateMachine $fsm)
{
    echo "Goodbye!";
}

$fsm = new FiniteStateMachine();
$factory = new \BankAccountFactory();
$factory->register("Обычный счёт", \accounts\BankAccount::class);
$factory->register("Дебетовый счёт", \accounts\CardBankAccount::class);
$factory->register("Депозитный счёт", \accounts\DepositBankAccount::class);
$fsm['factory'] = $factory;
$app = new Dispatcher($fsm);
$app->exception(\exceptions\NotEnoughMoney::class, "nomoney");
$app->exception(\exceptions\WithdrawalBlocked::class, "cooldawn");
$app->exception(\exceptions\CallbackIsNotCallbable::class, "command404");
$app->event_handler("startup", "start");
$app->event_handler("shutdown", "shutdown");
$app->register("*", "select_account_type", States::SelectAccountType);
$app->register("balance", "balance", States::OnlineLoop);
$app->register("in", "in", States::OnlineLoop);
$app->register("out", "out", States::OnlineLoop);
$app->run();

?>