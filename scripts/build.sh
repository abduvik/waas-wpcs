rm -rf build
mkdir build

cp -R packages/monday-host build/monday-host
cp -R packages/monday-client build/monday-client

rm -rf build/monday-host/vendor
rm -rf build/monday-client/vendor
rm -rf build/monday-client/data

(cd build/monday-host && composer install --no-dev)
(cd build/monday-client && composer install --no-dev)


(cd build && zip -r monday-host.zip monday-host)
(cd build && zip -r monday-client.zip monday-client)

rm -rf build/monday-host
rm -rf build/monday-client