<?php
namespace biu\lib;
/***************************************************************************
文件上传类
使用方法：
$aa=new upload($path);
$aa->set(文件大小，允许的后缀);
$bb=$aa->handle(上传的form名,宽,高);
 ***************************************************************************/
class upload{
    private $ext;
    private $size;
    private $error=null;
    private $path;//上传路径
    public function __construct($path){
        $this->path=$path;
    }
    public function error(){
        return $this->error;
    }
    /**
     * size 单位kb
     */
    public function set($size,$ext='jpg,png,bmp,gif,jpeg'){
        $this->ext=explode(',',$ext);
        $this->size=$size;
    }
   /* //传入到临时目录
    public function intemp($sFormName,$dstW=0,$dstH=0){
        $now=date('Y-m-d').'-'.time();
        return  $this->handle($sFormName,$this->path,$now,$dstW,$dstH);
    }*/

    /**
     * 上传处理
     * @param $sFormName
     * @param $path
     * @param int $dstW
     * @param int $dstH
     * @return bool|string
     */
    public function handle($sFormName,$path,$dstW=0,$dstH=0){
        if (empty($_FILES)){
            $this->error='上传错误';
            return false;
        }
        $allowExt =$this->ext;//允许上传的后缀名
        $fileName =$_FILES[$sFormName]['name'];//上传文件名
        $tempFile =$_FILES[$sFormName]['tmp_name'];//系统的上传临时目录
        $fileSize =$_FILES[$sFormName]['size'];//上传文件大小
        $fileParts = pathinfo($fileName);
        $ext=$fileParts['extension'];
        //$uploadFile=$path.'/'.$fileName.'.'.$ext;
        if(!in_array(strtolower($ext),$allowExt)){
            $this->error='文件类型错误';
        }elseif(!is_uploaded_file($tempFile)){
            $this->error='非法上传文件';
        }elseif(($fileSize/1024/1024)>$this->size){
            $this->error='上传文件过大';
        }else{
            /***********************************************************/
            $newName = time().mt_rand(1000,9999);
            $fileName = $newName.'.'.$ext;
            $uploadFile=$path.'/'.$fileName;
            /************************************************************/
            if(!$ok=move_uploaded_file($tempFile,$uploadFile)){ //上传文件
                $this->error='上传失败';
            }
            if($dstW>0 && $dstH>0){//如果给定了宽高，则视为缩小上传
                $smallFile  =$path.'/'.$fileName.'-SM.'.$ext;//缩略图保存地址
                //获取格式
                switch($ext){
                    case 'jpeg':
                        $src_image=imagecreatefromjpeg($uploadFile);
                        break;
                    case 'png':
                        $src_image=imagecreatefrompng($uploadFile);
                        break;
                    case 'bmp':
                        $src_image=imagecreatefromwbmp($uploadFile);
                        break;
                    case 'gif':
                        $src_image=imagecreatefromwbmp($uploadFile);
                        break;
                    default:
                        $src_image=imagecreatefromjpeg($uploadFile);
                        break;
                }
                //$src_image=ImageCreateFromJPEG($uploadFile);
                $srcW=imagesx($src_image); //获得图片宽
                $srcH=imagesy($src_image); //获得图片高
                $dst_image=imagecreatetruecolor($dstW,$dstH);
                /* //分配颜色 + alpha，将颜色填充到新图上
                $alpha = imagecolorallocatealpha($dst_image,0,0,0,0);
                imagefill($dst_image,0,0,$alpha);   */
                //设置颜色
                $color=imagecolorallocate($dst_image,255,255,255);
                imagecolortransparent($dst_image,$color);
                imagefill($dst_image,0,0,$color);
                imagecopyresized($dst_image,$src_image,0,0,0,0,$dstW,$dstH,$srcW,$srcH);
                $ok1=imagejpeg($dst_image,$smallFile);
                if($ok1 && unlink($uploadFile)){//如果上传成功，则删除临时大文件，保留缩略图
                    return  $smallFile;//返回小图路径
                }
            }
            return $uploadFile;
        }
        return false;
    }
}
?>
