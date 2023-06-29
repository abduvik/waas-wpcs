rm -rf build
mkdir build

cp -R packages/waas-host build/waas-host
cp -R packages/waas-client build/waas-client

rm -rf build/waas-host/vendor
rm -rf build/waas-client/vendor
rm -rf build/waas-client/data

(cd build/waas-host && composer install --no-dev)
(cd build/waas-client && composer install --no-dev)

(cd build && zip -r wpcs-waas-host.zip waas-host)
(cd build && zip -r wpcs-waas-client.zip waas-client)

WAAS_CLIENT_VERSION=$(cat ./build/waas-client/index.php | grep Version | head -1 | awk '{print $2}')
sed "s/%WPCS_WAAS_CLIENT_PLUGIN_VERSION%/$WAAS_CLIENT_VERSION/g" wpcs-waas-client-info.template.json > ./build/wpcs-waas-client-info.json

WAAS_HOST_VERSION=$(cat ./build/waas-host/index.php | grep Version | head -1 | awk '{print $2}')
sed "s/%WPCS_WAAS_HOST_PLUGIN_VERSION%/$WAAS_HOST_VERSION/g" wpcs-waas-host-info.template.json > ./build/wpcs-waas-host-info.json

rm -rf build/waas-host
rm -rf build/waas-client
