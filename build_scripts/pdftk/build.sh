mkdir pdfk_build
cd pdfk_build
apt-get update
apt-get download pdftk
apt-get download libgcj14
apt-get download libgcj-common

dpkg -x pdftk_2.01-1_amd64.deb .
dpkg -x libgcj14_4.8.2-19ubuntu1_amd64.deb .
dpkg -x libgcj-common_1%3a4.8.2-1ubuntu6_all.deb .

mkdir /worker/bin
mkdir /worker/lib

cp -rav usr/bin/* /worker/bin
cp -rav usr/lib/* /worker/lib
mv /worker/bin/pdftk /worker/bin/pdftk.target
echo '#!/bin/bash' >> /worker/bin/pdftk
echo "" >> /worker/bin/pdftk
echo 'export PATH=/worker/bin:$PATH;' >> /worker/bin/pdftk
echo 'export LD_LIBRARY_PATH=/worker/lib:/worker/lib/x86_64-linux-gnu:/usr/local/lib:$LD_LIBRARY_PATH;' >> /worker/bin/pdftk
echo "" >> /worker/bin/pdftk
echo '/worker/bin/pdftk.target "$@"' >> /worker/bin/pdftk
chmod +x /worker/bin/pdftk
cd ..
rm -rf pdfk_build
