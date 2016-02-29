<?PHP
	set_time_limit(0);
	echo "Starting script.php\n";
	class nginxUpstreams {
		public function __construct($ip, $bucket, $region, $availability_zone, $interval) {
			$this->bucket = $bucket;
			$this->availability_zone = $availability_zone;
			$this->lastHash = null;
			$this->region = $region;
			$this->first = true;
			$this->nginx = file_get_contents("nginx-config");

			while (TRUE) {
				$this->containers = $this->getAllContainers();
				$hash = md5(json_encode($this->containers));
				if ($hash !== $this->lastHash) {
					$config = $this->generateConfig();
					echo $config;
					file_put_contents("/etc/nginx/sites-enabled/default", $config);
					if ($this->first) {
						echo shell_exec("service nginx restart");
					}
					ELSE {
						echo shell_exec("service nginx reload");
					}
					$this->first = false;
					$this->lastHash = $hash;
				}
				sleep($interval);
			}
		}
		private function generateConfig() {
			$config = $this->nginx;
			//Don't repeat if no change has been made
			preg_match_all("/upstream ([_a-zA-Z0-9\-\.]+)\s?\{([^\}]+)?\}/msi", $this->nginx, $out);
			for ($i = 0; $i < count($out[0]); $i++) {
				$config = str_replace($out[0][$i], $this->getUpstream($out[1][$i]), $config);
			}
			return $config;
		}
		private function getContainers($image_id) {
			$arr = array();
			for ($i = 0; $i < count($this->containers); $i++) {
				if (($this->containers[$i]['container_name'] == $image_id) && ($this->containers[$i]['availability_zone'] == $this->availability_zone)) {
					$arr[] = $this->containers[$i];
				}
			}
			return $arr;
		}
		private function getUpstream($image_id) {
			$str = "upstream ".$image_id." {\n";
			$containers = $this->getContainers($image_id);
			for ($i = 0; $i < count($containers); $i++) {
				$ip = reset(explode(":", $containers[$i]['ports']));
				$port = end(explode(":", reset(explode("-", $containers[$i]['ports']))));
				$str .= "\tserver ".$ip.":".$port.";\n";
			}
			$str .= "\n}";
			return $str;
		}
		private function getAllContainers() {
			shell_exec("aws s3api get-object --bucket ".$this->bucket." --key containers_".$this->region.".json containers.json");
			if (file_exists("containers.json")) {
				return json_decode(file_get_contents("containers.json"), true);
			}
			return array();
		}
	}
	new nginxUpstreams($argv[1], $argv[2], $argv[3], $argv[4], $argv[5]); 
?>
