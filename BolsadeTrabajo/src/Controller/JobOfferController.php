<?php

namespace App\Controller;

use App\Entity\Applicant;
use App\Entity\JobOffer;
use App\Form\ApplicationType;
use App\Form\JobOfferType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/job/offer")
 */
class JobOfferController extends AbstractController
{
    /**
     * @IsGranted("ROLE_COMPANY_OWNER")
     * @Route("/", name="job_offer_index", methods={"GET"})
     */
    public function index(): Response
    {
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {

            return $this->redirectToRoute('company_create');
        }

        return $this->render('job_offer/index.html.twig', [
            'job_offers' => $company->getJobOffers(),
        ]);
    }

    /**
     * @IsGranted("ROLE_COMPANY_OWNER")
     * @Route("/new", name="job_offer_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $jobOffer = new JobOffer();
        $jobOffer->setCompany($this->getUser()->getCompany());
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($jobOffer);
            $entityManager->flush();

            return $this->redirectToRoute('job_offer_index');
        }

        return $this->render('job_offer/new.html.twig', [
            'job_offer' => $jobOffer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="job_offer_show", methods={"GET"})
     */
    public function show(JobOffer $jobOffer): Response
    {
        return $this->render('job_offer/show.html.twig', [
            'job_offer' => $jobOffer,
        ]);
    }

    /**
     * @Security("(is_granted('ROLE_COMPANY_OWNER') and jobOffer.getCompany() == user.getCompany()) or is_granted('ROLE_ADMIN')")
     * @Route("/{id}/edit", name="job_offer_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, JobOffer $jobOffer): Response
    {
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('job_offer_index');
        }

        return $this->render('job_offer/edit.html.twig', [
            'job_offer' => $jobOffer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("(is_granted('ROLE_COMPANY_OWNER') and jobOffer.getCompany() == user.getCompany()) or is_granted('ROLE_ADMIN')")
     * @Route("/{id}", name="job_offer_delete", methods={"DELETE"})
     */
    public function delete(Request $request, JobOffer $jobOffer, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$jobOffer->getId(), $request->request->get('_token'))) {
            $mail = new Email();
            $mail
                ->setBody($translator->trans('job_offer.delete.email.body', ['title' => $jobOffer->getTitle()]))
                ->subject($translator->trans('job_offer.delete.email.subject'))
                ->from($jobOffer->getEmail());
            foreach ($jobOffer->getApplicants() as $applicant) {
                $mail->to($applicant->getEmail());
                $mailer->send($mail);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($jobOffer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('job_offer_index');
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/{id}/apply", name="offer_apply")
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function apply(JobOffer $offer, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, TranslatorInterface $translator)
    {
        $applicant = new Applicant();
        $form = $this->createForm(ApplicationType::class, $applicant);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {
            $entityManager->persist($applicant);
            $entityManager->flush();

            $email = (new Email())
                ->from('Marioalonsomora84@gmail.com')
                ->to('Marioalonsomora84@gmail.com')
                ->cc('Marioalonsomora84@gmail.com')
                ->bcc('Marioalonsomora84@gmail.com')
                ->replyTo('Marioalonsomora84@gmail.com')
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Important Notification')
                ->text('Lorem ipsum...')
                ->html('<h1>Lorem ipsum</h1> <p>...</p>')
            ;

            return $this->redirectToRoute('home');
        }

        return $this->render("job_offer/apply.html.twig", ['offer' => $offer, 'form' => $form->createView()]);
    }
}