<?php

namespace App\Controller;

use App\Entity\Materiel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use FPDF;

class ModuleController extends AbstractController
{
    /**
     * @Route("/", name="liste")
     */
    public function index(): Response
    {
        $materiel = $this->getDoctrine()->getRepository(Materiel::class)->findAll();

        return $this->render('module/index.html.twig', [
            'controller_name' => 'ModuleController',
            'materiel' => $materiel,

        ]);
    }


    /**
     * @Route("/download", name="download")
     */
    public function exportExcel() {

        $materiel = $this->getDoctrine()->getRepository(Materiel::class)->findAll();

        $myVariableCSV = "Nom du produit; Quantité disponible; Prix du produit; Date de création du produit;\n";
        foreach ($materiel as $produit) {
            $myVariableCSV .= $produit->getNom().";".$produit->getQuantite().";".$produit->getPrix().";".$produit->getcreatedAt()->format('d/m/Y')."\n";
        }
        return new Response(
            $myVariableCSV,
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel',
                "Content-disposition" => "attachment; filename=Materiel.csv"
            ]
        );
    }


    /**
     * @Route("/create", name="create")
     * @Route("/{id}/modif", name="modif")
     */
    public function modif(Materiel $materiel = null,Request $request, EntityManagerInterface $manager){

        if(!$materiel){
            $materiel = new Materiel();
        }
        

        $form = $this->createFormBuilder($materiel)
                        ->add('nom')
                        ->add('prix')
                        ->add('quantite')
                        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!$materiel->getID()){
                $materiel->setCreatedAt(new \DateTime());
            }
            
            $manager->persist($materiel);
            $manager->flush();

            return $this->redirectToRoute('liste');
        }

        return $this->render('module/modif.html.twig', [
            'formMateriel' => $form->createView(),
            'modification' => $materiel->getID() !== null
        ]);
    }

    /**
     * @Route("/delete", name="delete")
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Materiel $materiel)
    {
        $em = $this->getDoctrine()->getManager();

        $materiel = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:Materiel')->Find($id);

        if($materiel != null)
        {
            $em->remove($materiel);
            $em->flush();
        }

        return $this->redirectToRoute('/');
    }


    /**
         * @Route("/{id}/pdf", name="pdf")
         */
        public function pdf(Materiel $materiel, $id) {

            $materiel = $this->getDoctrine()->getRepository(Materiel::class)->find($id);
            $nom = $materiel->getNom();
            $quantite = $materiel->getQuantite();
            $prix = $materiel->getPrix();
            $dateCreation = $materiel->getcreatedAt()->format('d/m/Y');

            $pdf = new \FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'U',16);
            $pdf->Cell(40,10, 'Details du produit');
            $pdf->Ln(10);
            $pdf->SetFont('Arial', '',14);
            $pdf->Cell(40,10,utf8_decode('Nom du produit : '));
            $pdf->Cell(70,10,utf8_decode($nom));
            $pdf->Ln(10);
            $pdf->Cell(40,10,utf8_decode('Prix du produit :'));
            $pdf->Cell(70,10,utf8_decode($prix));
            $pdf->Ln(10);
            $pdf->Cell(40,10,utf8_decode('Quantite en stock du produit :'));
            $pdf->Cell(70,10,utf8_decode($quantite));
            $pdf->Ln(10);
            $pdf->Cell(40,10,utf8_decode('Date de création :'));
            $pdf->Cell(70,10,utf8_decode($dateCreation));



            return new Response($pdf->Output(), 200, array(
                'Content-Type' => 'application/pdf'));

            return $this->render('module/pdf.html.twig', [
                'controller_name' => 'ModuleController',
                'materiel' => $materiel,
            ]);
        }


}