# Bss Update
[Blessing Skin](https://github.com/bs-community/blessing-skin-server) 更新镜像服务器，使用 PHP 开发。

## 安装配置
1. 将本仓库 clone 下来后，修改 index.php 顶部的 `STORAGE_ROOT` 改为你的网站实际地址
2. 配置 Nginx，伪静态可参考本仓库 rewrite.conf 文件的内容
3. 将 bss-update.service 文件放入 `/etc/systemd/system/`
4. 执行 `systemctl daemon-reload` 重载，然后执行 `systemctl enable bss-update --now` 运行并设置开机启动

## 实际用法
```
https://yourdomain.com/bss-update/{channel}/{version}/{subversion}/{build}
```

其中可选参数：
- channel 更新通道，可以是 `stable`，`rc`，`beta`, `alpha`
- version 主要版本号，目前有 `4`，`5`，`6`
- subversion 详细版本号，例如 `5.2.0`
- build 构建编号，例如 1，对应的就是 `6.0.0-rc.1` 中的最后一位

如果不带任何参数，则默认 channel 为 `stable`，version 为 `5`。

## 开源协议
本项目使用 MIT 协议开源。
