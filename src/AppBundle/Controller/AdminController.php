<?php
namespace AppBundle\Controller;

//use Guzzle\Http\Message\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use AppBundle\Entity;
use Symfony\Component\Validator\Constraints\Time;

//use Liuggio\ExcelBundle;

//use Symfony\Component\Validator\Constraints\Page;

class AdminController extends Controller
{
    protected $pageSize = 30;
    //页面模板地址
    protected $pageTemplate = array(
        'page.html.twig' => 'page.html.twig'
    );

    /**
     * @Route("/admin/", name="admin_index")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:admin:index.html.twig');
    }
     /**
     * @Route("/admin/app/{uid}", name="admin_app_log")
     */
    public function appLogAction(Request $request, $uid = null)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:AppLog');
        $queryBuilder = $repository->createQueryBuilder('a');
        $queryBuilder->orderBy('a.createTime', 'DESC');
        if(null != $uid){
            $user = $this->getDoctrine()->getRepository('AppBundle:WechatUser')->find($uid);
            $queryBuilder->where('a.user = :user');
            $queryBuilder->setParameters(array('user'=>$user));
        }
        $query = $queryBuilder->getQuery();
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),/*page number*/
            $this->pageSize
        );
        return $this->render('AppBundle:admin:log.html.twig', array('pagination'=>$pagination));
    }
    /**
     * @Route("/admin/timeline/{uid}", name="admin_timeline_log")
     */
    public function timelineLogAction(Request $request, $uid = null)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:TimelineLog');
        $queryBuilder = $repository->createQueryBuilder('a');
        if(null != $uid){
            $user = $this->getDoctrine()->getRepository('AppBundle:WechatUser')->find($uid);
            $queryBuilder->where('a.user = :user');
            $queryBuilder->setParameters(array('user'=>$user));
        }
        $queryBuilder->orderBy('a.createTime', 'DESC');
        $query = $queryBuilder->getQuery();
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),/*page number*/
            $this->pageSize
        );
        return $this->render('AppBundle:admin:log.html.twig', array('pagination'=>$pagination));
    }
     /**
     * @Route("/admin/user/", name="admin_user")
     */
    public function wechatUserAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:WechatUser');
        $queryBuilder = $repository->createQueryBuilder('a');
        $queryBuilder->orderBy('a.createTime', 'DESC');
        $query = $queryBuilder->getQuery();
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),/*page number*/
            $this->pageSize
        );
        return $this->render('AppBundle:admin:wechatUser.html.twig', array('pagination'=>$pagination));
    }
    /**
     * @Route("/admin/account/", name="admin_account")
     */
    public function accountAction(Request $request)
    {
        $account = $this->getUser();
        $form = $this->createFormBuilder($account, array('validation_groups'=>array('Account')))
            ->add('email', 'text')
            ->add('password', 'repeated',array(
                'type' => 'password',
                'invalid_message' => '两次输入的密码必须一直.',
                'required' => true,
                'first_options'  => array('label' => '密码'),
                'second_options' => array('label' => '重复密码'),
            ))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($account);
            $password = $encoder->encodePassword($data->getPassword(), $account->getSalt());
            $account->setPassword($password);
            $account->setEmail($data->getEmail());
            $em->persist($account);
            $em->flush();
            $this->addFlash('success','恭喜，提交成功！点击左侧菜单进行其他操作。');

        }
        return $this->render('AppBundle:admin:account.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    /**
     * @Route("/admin/export/", name="admin_export")
     */
    public function exportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:LotteryLog');
        $queryBuilder = $repository->createQueryBuilder('a')
            ->leftjoin('a.lottery','b')
            ->leftjoin('b.prize','c');
        $queryBuilder->where('c.id != 5');
        $queryBuilder->orderBy('a.createTime', 'DESC');
        $logs = $queryBuilder->getQuery()->getResult();
        //$output = '';
        $arr = array(
            'id,奖项,姓名,手机,地址,抽奖时间,抽奖IP'
        );
        foreach($logs as $v){
            $member = $em->getRepository('AppBundle:Member')->findOneBySessionId($v->getSessionId());
            $_string = $v->getId().','.$v->getLottery()->getPrize()->getTitle().',';
            if( isset($member))
                $_string .= $member->getName().','.$member->getTel().','.$member->getAddress().',';
            else
                $_string .= '-,-,-,';
            $_string .= $v->getCreateTime()->format('Y-m-d H:i:s').','.$v->getCreateIp().',';
            $arr[] = $_string;
        }
        $output = implode("\n", $arr);

        //$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        /*
        $phpExcelObject = new \PHPExcel();
        $phpExcelObject->getProperties()->setCreator("liuggio")
            ->setLastModifiedBy("Giulio De Donato")
            ->setTitle("Office 2005 XLSX Test Document")
            ->setSubject("Office 2005 XLSX Test Document")
            ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");
        $phpExcelObject->setActiveSheetIndex(0);
        foreach($logs as $v){
            $phpExcelObject->setCellValue('A1', $v->getId());
        }
        $phpExcelObject->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'stream-file.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        */

        $response = new Response($output);
        $response->headers->set('Content-Disposition', ':attachment; filename=data.csv');
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;
    }

}
