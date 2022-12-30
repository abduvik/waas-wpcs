rm -rf build
mkdir build

cp -R packages/waas-host build/waas-host
cp -R packages/waas-client build/waas-client

rm -rf build/waas-host/vendor
rm -rf build/waas-client/vendor
rm -rf build/waas-client/data

(cd build/waas-host && composer install --no-dev)
(cd build/waas-client && composer install --no-dev)


(cd build && zip -r waas-host.zip waas-host)
(cd build && zip -r waas-client.zip waas-client)

rm -rf build/waas-host
rm -rf build/waas-client