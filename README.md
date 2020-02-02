# qk-token-system
夸克区块链token充提系统

## env配置

RPC_HOST=http://127.0.0.1:8545 #区块同步rpc

WITHDRAW_RPC_HOST=http://127.0.0.1:8545 #提现rpc

ADDRESS=0x0000000000000000000000000000000000000001 #托管地址

PASSWORD=123456 #托管地址密码

## 定时任务

php artisan  doSync #同步区块数据

php artisan  autoRecharge #充值

php artisan  checkbox #开箱子

