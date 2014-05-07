<?php
/*
* title : AWS sdk class
* date  : 2014.02.05
* refer URL	: http://docs.aws.amazon.com/aws-sdk-php/guide/latest/service-s3.html
*							http://docs.aws.amazon.com/AmazonS3/latest/dev/ACLOverview.html
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');
use Aws\S3\S3Client;

class AwsS3 {
  private $aws_ins    = null;             // aws instance
  private $aws_key    = 'AKIAJ3YHP7U5NCLUPNBQ';
  private $aws_secret = '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';
  private $s3_ins     = null;
  private $bucket	  = 'gracevine.video';

  function __construct($key='', $secret='', $region='') {
    if(trim($key)=='') {
      $key = $this->aws_key;
    }
    if(trim($secret)=='') {
      $secret = $this->aws_secret;
    }

    // Instantiate the S3 client with your AWS credentials
    $this->aws_ins = S3Client::factory(array(
      'key'    => $key,
      'secret' => $secret,
    ));
		$this->setS3();
  }

	/*
	*	set bucket name
	*	@param	string; S3 bucket name
	*	@return	boolean
	*/
	function setBucket($bname) {
		if(trim($bname)!='') {
			$this->bucket = $bname;
			return true;
		}
		else {
			return false;
		}
	}

  function getInstance() {
    return $this->aws_ins;
  }

  function setS3() {
    return $this->s3_ins = $this->aws_ins->get('S3');
  }

	/*
	*	get list of bucket
	*	@param	void;
	*	@return	array | false
	*/
  function getFileList($bucket='') {
    if($this->s3_ins!='') {
			//if($bucket=='')
			$bucket = $this->bucket;

			$iterator = $this->aws_ins->getIterator('ListObjects', array(
					'Bucket' => $bucket
			));

			foreach ($iterator as $object) {
					$ret[] = $object['Key'];
			}
			return $ret;
    }
    else {
      return false;
    }
  }


	/*
	*	upload file into S3 server
	*	@param	: string; local file full path
	*	@param	: string; file content
	*	@return	: string; uploaded file url
	*/
	function uploadFile($orig_path,$target_path,$content='') {
		if(file_exists($orig_path)==true) {
			$tem = explode('/',$orig_path);
			$fname	= array_pop($tem);
			$pathToFile = implode('/',$tem);
			if($target_path)
			$target_path ='/'.$target_path;
			if(trim($content)!='') {
				$result = $this->aws_ins->putObject(array(
						'Bucket' => $this->bucket.$target_path,
						'Key'    => $fname,
						'Body'   => $content,
						'ACL'    => 'public-read'
				));
			}
			else {
				$result = $this->aws_ins->putObject(array(
						'Bucket' =>	$this->bucket.$target_path,
						'Key'    => $fname,
						'SourceFile' => $orig_path,
						'Metadata'   => array(),
						'ACL'        => 'public-read'
				));
			}
			return urldecode($result['ObjectURL']);
		}
		else {
			return false;
		}
	}

	/*
	*	remove file in S3 server
	*	@param	string; file name in S3 server e.g. (dirname/a.jpg or a.jpg)
	*	@return	boolean;
	*/
	function removeFile($fname) {
		if(trim($fname)!='') {
			$ret = $this->aws_ins->deleteObject(array(
					'Bucket'	=> $this->bucket,
					'Key'			=> $fname
			));
			return $ret;
		}
		else {
			return false;
		}
	}
}

set_time_limit(0);
$AWS = new AwsS3;
//$row = $AWS->uploadFile('public/videos/mikethefrog.mp4','2014/05/07');/*orig_path,target_path*/
//$row = $AWS->removeFile('/2014/05/07/mikethefrog.mp4');
//print_r($row);
?>