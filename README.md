
# OpenTelemetry Doctrine auto-instrumentation

Please read https://opentelemetry.io/docs/instrumentation/php/automatic/ for instructions on how to
install and configure the extension and SDK.

## Overview
Auto-instrumentation hooks are registered via composer, and spans will automatically be created for
selected `Doctrine\DBAL\Driver\Connection` methods.
This library is inspired by the [PDO auto-instrumentation](https://github.com/opentelemetry-php/contrib-auto-pdo)