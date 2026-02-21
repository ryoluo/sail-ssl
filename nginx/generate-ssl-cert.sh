#!/bin/sh

ME=$(basename $0)
CERTS_DIR=/etc/nginx/certs
ROOT_KEY=$CERTS_DIR/root-ca.key
ROOT_CERT=$CERTS_DIR/root-ca.crt
KEY=$CERTS_DIR/server.key
CERT=$CERTS_DIR/server.pem
CSR=$CERTS_DIR/server.csr
EXT_FILE=$CERTS_DIR/server-ext.cnf
CN=$SSL_DOMAIN
SAN=$SSL_ALT_NAME

if [ -f $KEY ] && [ -f $CERT ]; then
    echo "$ME: Server certificate already exists, do nothing."
else
    openssl req -x509 -newkey rsa:2048 -keyout $ROOT_KEY -out $ROOT_CERT \
        -sha256 -days 3650 -nodes -subj "/CN=$CN Root CA"

    openssl req -newkey rsa:2048 -keyout $KEY -out $CSR \
        -nodes -subj "/CN=$CN"

    {
        echo "basicConstraints = CA:FALSE"
        echo "keyUsage = digitalSignature, keyEncipherment"
        echo "extendedKeyUsage = serverAuth"
        if [ -n "$SAN" ]; then
            echo "subjectAltName = $SAN"
        fi
    } > $EXT_FILE

    openssl x509 -req -in $CSR -CA $ROOT_CERT -CAkey $ROOT_KEY \
        -CAcreateserial -out $CERT -sha256 -days 3650 -extfile $EXT_FILE

    rm -f $CSR $EXT_FILE $CERTS_DIR/root-ca.srl
    echo "$ME: Root CA and server certificate have been generated."
    echo "$ME: Import $ROOT_CERT into your browser to trust the certificate."
fi
