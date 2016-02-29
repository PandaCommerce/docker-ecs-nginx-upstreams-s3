#!/bin/sh
export LOCAL_IP=$(curl -s http://169.254.169.254/latest/meta-data/local-ipv4)
export MY_AZ=$(curl -s http://169.254.169.254/latest/meta-data/placement/availability-zone)

echo $1 >> nginx-config

mkdir $HOME/.aws
echo "[default] 
region = $AWS_DEFAULT_REGION 
aws_access_key_id = $AWS_ACCESS_KEY_ID  
aws_secret_access_key = $AWS_SECRET_ACCESS_KEY " > $HOME/.aws/config

echo php script.php $LOCAL_IP $S3_BUCKET $AWS_DEFAULT_REGION $MY_AZ $INTERVAL
exec php script.php $LOCAL_IP $S3_BUCKET $AWS_DEFAULT_REGION $MY_AZ $INTERVAL
