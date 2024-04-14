# Generate a random password
random_password="$(openssl rand -base64 32)"
email="email1@to2mor.com"

# Create the new TMweb user
/usr/sbin/useradd "TMweb" -c "$email" --no-create-home

# do not allow login into TMweb user
echo TMweb:$random_password | sudo chpasswd -e

mkdir -p /etc/sudoers.d
cp -f /usr/local/TM/web/sudo/TMweb /etc/sudoers.d/
chmod 440 /etc/sudoers.d/TMweb
