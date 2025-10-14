<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// DATABASE
use App\Entity\Color;
use App\Entity\Finish;
use App\Entity\Material;
use App\Entity\User;
use App\Entity\UserOrder;

// Repositories
use App\Repository\MaterialRepository;
use App\Repository\FinishRepository;
use App\Repository\ColorRepository;
use App\Repository\UserRepository;

// Adding Users
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

// Validation
use Symfony\Component\Validator\Validator\ValidatorInterface;

// FILE SHIT
use Symfony\Component\HttpFoundation\File\Exception\FileException;



final class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/admin/orders', name: 'admin_orders')]
    public function orders(): Response
    {
        return $this->render('admin/orders.html.twig');
    }



    #****************************************USERS****************************************#
    #https://chatgpt.com/c/68d265c4-7ea8-8332-9ef4-ca7b35bdb23a
        # CREATE
        #[Route('/admin/add-user', name: 'admin_add_user', methods: ['POST'])]
        public function addUser(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
        {
            $user = new User();
            $user->setUsername($request->request->get('username'));
            $user->setPassword(password_hash($request->request->get('password'), PASSWORD_BCRYPT));
            $user->setFullName($request->request->get('fullName'));
            $user->setEmail($request->request->get('email'));
            $user->setAddress($request->request->get('address'));
            $user->setCreatedAt(new \DateTime());
            $user->setIsAdmin($request->request->get('role') === 'admin');

            //Validate
            $errors = $validator->validate($user);

            if (count($errors) > 0) 
            {
                // You can return errors or flash messages
                $errorMessages = [];
                foreach ($errors as $error) 
                {
                    $errorMessages[] = $error->getMessage();
                }

                $this->addFlash('error', implode('<br>', $errorMessages));
                return $this->redirectToRoute('admin_users');
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User added successfully!');
            return $this->redirectToRoute('admin_users');
        }
        
        # READ
        #[Route('/admin/users', name: 'admin_users')]
        public function users(UserRepository $userRepository): Response
        {
            $users = $userRepository->findAll();

            $totalUsers = count($users);
            $totalAdmins = 0;
            $totalCustomers = 0;

            foreach ($users as $user) 
            {
                if ($user->isAdmin()) 
                {
                    $totalAdmins++;
                } 
                else 
                {
                    $totalCustomers++;
                }
            }

            return $this->render('admin/users.html.twig', [
                'users' => $users,
                'totalUsers' => $totalUsers,
                'totalAdmins' => $totalAdmins,
                'totalCustomers' => $totalCustomers,
            ]);
        }

        # UPDATE
        #[Route('/admin/edit-user/{id}', name: 'admin_edit_user', methods: ['POST'])]
        public function editUser(Request $request, User $user, EntityManagerInterface $em): Response
        {
            $user->setUsername($request->request->get('username'));
            $user->setEmail($request->request->get('email'));
            $user->setFullName($request->request->get('fullName'));
            $user->setAddress($request->request->get('address'));
            $user->setIsAdmin($request->request->get('role') === 'admin');

            $newPassword = $request->request->get('password');
            if (!empty($newPassword)) 
            {
                $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));
            }

            $em->flush();

            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('admin_users');
        }

        # DELETE
        #[Route('/admin/delete-user/{id}', name: 'admin_delete_user', methods: ['POST'])]
        public function deleteUser(User $user, EntityManagerInterface $em, Request $request): Response
        {
            if ($this->isCsrfTokenValid('delete_user', $request->request->get('_token'))) 
            {
                $em->remove($user);
                $em->flush();
                $this->addFlash('success', 'User deleted successfully!');
            } 
            else 
            {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            return $this->redirectToRoute('admin_users');
        }
    #****************************************USERS****************************************#



    #****************************************MATERIALS****************************************#
        # CREATE
        #[Route('/admin/add-material', name: 'admin_add_material', methods: ['POST'])]
        public function addMaterial(Request $request, EntityManagerInterface $em): Response
        {
            $material = new Material();

            // --- Set basic fields ---
            $material->setName($request->request->get('material_name'));
            $material->setPrice((float)$request->request->get('price'));
            $material->setDetails($request->request->get('description'));
            $material->setProperties($request->request->get('properties') ?? '');
            $material->setAvailability($request->request->get('available') === 'on');

            // GETTING THE ID OF THE MATERIAL (PUSHING THE DATA TO DATABASE TO GET ID)
            $em->persist($material);
            $em->flush();

            // IF THERE IS AN IMAGEFILE
            $imageFile = $request->files->get('image');
            if ($imageFile) 
            {
                // LIMITER
                $maxSize = 5 * 1024 * 1024; // (SIZE) 5 MB
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp']; //IMGS TYPES
                
                // LOCATION OF THE PROJ, THEN PUBLIC
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/Materials';

                // Validate file size
                if ($imageFile->getSize() > $maxSize) 
                {
                    $this->addFlash('error', 'Image too large (max 5 MB).');
                    return $this->redirectToRoute('admin_materials');
                }
                // Validate file type
                if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) 
                {
                    $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                    return $this->redirectToRoute('admin_materials');
                }


                // MAKING GIBBERISH
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME); //FOR FUTURE USE IF NEEDED ORIG NAME (BUT NAH)
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);

                //FORMAT EX: material_12_PLA_sample_1738983517.jpg
                $newFilename = sprintf
                (
                    'material_%d_%s_%s.%s',
                    $material->getId(),
                    $safeName,
                    time(),
                    $imageFile->guessExtension()
                );

                // Make sure upload directory exists
                if (!file_exists($uploadsDir)) 
                {
                    mkdir($uploadsDir, 0777, true);
                }

                // Move the uploaded file
                try 
                {
                    $imageFile->move($uploadsDir, $newFilename);
                    $material->setImagePath('uploads/Materials/' . $newFilename);
                } 
                catch (\Exception $e) 
                {
                    $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                    return $this->redirectToRoute('admin_materials');
                }
            } 
            else 
            {
                // If no file uploaded, set imagePath to null
                $material->setImagePath(null);
            }
            
            $em->flush();
            return $this->redirectToRoute('admin_materials');
        }

        # READ
        #[Route('/admin/materials', name: 'admin_materials')]
        public function materials(MaterialRepository $materialRepository): Response
        {
            $materials = $materialRepository->findAll();

            return $this->render('admin/materials.html.twig',[
                'materials' => $materials,
            ]);

        }

        # UPDATE
        #[Route('/admin/edit-material/{id}', name: 'admin_edit_material', methods: ['POST'])]
        public function editMaterial(Request $request, Material $material, EntityManagerInterface $em): Response
        {
            $material->setName($request->request->get('material_name'));
            $material->setPrice((float)$request->request->get('price'));
            $material->setDetails($request->request->get('description'));
            $material->setProperties($request->request->get('properties') ?? '');
            $material->setAvailability($request->request->get('available') === 'on');

            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/Materials';
            $imageFile = $request->files->get('image');
            $removeImage = $request->request->get('remove_image');

            // If "Remove Image" is checked
            if ($removeImage && $material->getImagePath()) {
                $oldFile = $this->getParameter('kernel.project_dir') . '/public/' . $material->getImagePath();
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
                $material->setImagePath(null);
            }

            // If a new image was uploaded
            if ($imageFile) {
                $maxSize = 5 * 1024 * 1024; // 5 MB
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if ($imageFile->getSize() > $maxSize) {
                    $this->addFlash('error', 'Image too large (max 5 MB).');
                    return $this->redirectToRoute('admin_materials');
                }
                if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) {
                    $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                    return $this->redirectToRoute('admin_materials');
                }

                // Delete old image first
                if ($material->getImagePath()) {
                    $oldFile = $this->getParameter('kernel.project_dir') . '/public/' . $material->getImagePath();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Generate safe filename
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                $newFilename = sprintf(
                    'material_%d_%s_%s.%s',
                    $material->getId(),
                    $safeName,
                    time(),
                    $imageFile->guessExtension()
                );

                if (!file_exists($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                try {
                    $imageFile->move($uploadsDir, $newFilename);
                    $material->setImagePath('uploads/Materials/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                    return $this->redirectToRoute('admin_materials');
                }
            }

            $em->flush();

            $this->addFlash('success', 'Material updated successfully!');
            return $this->redirectToRoute('admin_materials');
        }


        # DELETE
        #[Route('/admin/delete-material/{id}', name: 'admin_delete_material', methods: ['POST'])]
        public function deleteMaterial(Material $material, EntityManagerInterface $em, Request $request): Response
        {
            if ($this->isCsrfTokenValid('delete_material', $request->request->get('_token'))) 
            {
                // Check if material has an image
                if ($material->getImagePath()) {
                    $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $material->getImagePath();

                    // Delete the image file if it exists
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                // Remove material from database
                $em->remove($material);
                $em->flush();

                $this->addFlash('success', 'Material and its image deleted successfully!');
            } 
            else 
            {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            return $this->redirectToRoute('admin_materials');
        }
    #****************************************MATERIALS****************************************#



    #****************************************FINISHES****************************************#
        # CREATE
        #[Route('/admin/add-finish', name: 'admin_add_finish', methods: ['POST'])]
        public function addFinish(Request $request, EntityManagerInterface $em): Response
        {
            $finish = new Finish();

            $finish->setName($request->request->get('finish_name'));
            $finish->setPrice((float)$request->request->get('price'));
            $finish->setDetails($request->request->get('details'));
            $finish->setProperties($request->request->get('properties') ?? '');
            $finish->setAvailability($request->request->get('available') === 'on');

            $em->persist($finish);
            $em->flush();

            // Handle Image
            $imageFile = $request->files->get('image');
            if ($imageFile) 
            {
                $maxSize = 5 * 1024 * 1024;
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/Finishes';

                if ($imageFile->getSize() > $maxSize) 
                {
                    $this->addFlash('error', 'Image too large (max 5 MB).');
                    return $this->redirectToRoute('admin_finishes');
                }

                if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) 
                {
                    $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                    return $this->redirectToRoute('admin_finishes');
                }

                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                $newFilename = sprintf(
                    'finish_%d_%s_%s.%s',
                    $finish->getId(),
                    $safeName,
                    time(),
                    $imageFile->guessExtension()
                );

                if (!file_exists($uploadsDir)) 
                {
                    mkdir($uploadsDir, 0777, true);
                }

                try 
                {
                    $imageFile->move($uploadsDir, $newFilename);
                    $finish->setImagePath('uploads/Finishes/' . $newFilename);
                } catch (\Exception $e) 
                {
                    $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                    return $this->redirectToRoute('admin_finishes');
                }
            } 
            else 
            {
                $finish->setImagePath(null);
            }

            $em->flush();
            return $this->redirectToRoute('admin_finishes');
        }

        # READ
        #[Route('/admin/finishes', name: 'admin_finishes')]
        public function finishes(FinishRepository $finishRepository): Response
        {
            $finishes = $finishRepository->findAll();

            return $this->render('admin/finishes.html.twig', [
                'finishes' => $finishes,
            ]);
        }

        # UPDATE
        #[Route('/admin/edit-finish/{id}', name: 'admin_edit_finish', methods: ['POST'])]
        public function editFinish(Finish $finish, Request $request, EntityManagerInterface $em): Response 
        {
            $finish->setName($request->request->get('finish_name'));
            $finish->setDetails($request->request->get('details'));
            $finish->setPrice((float)$request->request->get('price'));
            $finish->setProperties($request->request->get('properties', ''));
            $finish->setAvailability($request->request->has('available'));

            // Handle image upload or removal
            $removeImage = $request->request->get('remove_image');
            $imageFile = $request->files->get('image');

            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/finishes/';

            if ($removeImage && $finish->getImagePath()) 
            {
                $oldPath = $this->getParameter('kernel.project_dir') . '/public/' . $finish->getImagePath();
                if (file_exists($oldPath)) unlink($oldPath);
                $finish->setImagePath(null);
            }

            if ($imageFile) 
            {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($uploadsDir, $newFilename);
                $finish->setImagePath('uploads/finishes/' . $newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Finish updated successfully!');
            return $this->redirectToRoute('admin_finishes');
        }


        # DELETE
        #[Route('/admin/delete-finish/{id}', name: 'admin_delete_finish', methods: ['POST'])]
        public function deleteFinish(Finish $finish, EntityManagerInterface $em, Request $request): Response
        {
            if ($this->isCsrfTokenValid('delete_finish', $request->request->get('_token'))) 
            {
                // Check if finish has an image
                if ($finish->getImagePath()) 
                {
                    $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $finish->getImagePath();

                    // Delete the image file if it exists
                    if (file_exists($filePath)) 
                    {
                        unlink($filePath);
                    }
                }

                // Remove finish from database
                $em->remove($finish);
                $em->flush();

                $this->addFlash('success', 'Finish and its image deleted successfully!');
            } 
            else 
            {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            return $this->redirectToRoute('admin_finishes');
        }
    #****************************************FINISHES****************************************#



    #****************************************COLORS****************************************#
        #[Route('/admin/add-colors', name: 'admin_add_colors', methods: ['POST'])]
        public function addColors(Request $request, EntityManagerInterface $em): Response
        {
            $color = new Color();

            $color->setName($request->request->get('colors_name'));
            $color->setPrice((float)$request->request->get('price'));
            $color->setAvailability($request->request->get('available') === 'on');

            $em->persist($color);
            $em->flush();

            // (Hidden shit outside form (if hex or image input))
            $appearanceType = $request->request->get('appearance_type');

            // IF HEX COLOR
            if ($appearanceType === 'hex') 
            {
                $color->setColorHex($request->request->get('color_hex'));
                $color->setImagePath(null);
            } 
            elseif ($appearanceType === 'image') // IF IMG COLOR
            {
                $imageFile = $request->files->get('image');
                if ($imageFile) 
                {
                    $maxSize = 5 * 1024 * 1024;
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/Colors';

                    if ($imageFile->getSize() > $maxSize) 
                    {
                        $this->addFlash('error', 'Image too large (max 5 MB).');
                        return $this->redirectToRoute('admin_colors');
                    }

                    if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) 
                    {
                        $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                        return $this->redirectToRoute('admin_colors');
                    }

                    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                    $newFilename = sprintf(
                        'color_%d_%s_%s.%s',
                        $color->getId(),
                        $safeName,  
                        time(),
                        $imageFile->guessExtension()
                    );

                    if (!file_exists($uploadsDir)) 
                    {
                        mkdir($uploadsDir, 0777, true);
                    }
                
                    try 
                    {
                        $imageFile->move($uploadsDir, $newFilename);
                        $color->setImagePath('uploads/Colors/' . $newFilename);
                    } 
                    catch (\Exception $e) 
                    {
                        $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                        return $this->redirectToRoute('admin_colors');
                    }
                }
                else 
                {
                    $color->setImagePath(null);
                }

                $color->setColorHex(null);
            }

            $em->persist($color);
            $em->flush();

            return $this->redirectToRoute('admin_colors');
        }

        # READ
        #[Route('/admin/colors', name: 'admin_colors')]
        public function colors(colorRepository $colorRepository): Response
        {
            $colors = $colorRepository->findAll();

            return $this->render('admin/colors.html.twig',[
                'colors' => $colors,
            ]);
        }

        # UPDATE
        #[Route('/admin/edit-color/{id}', name: 'admin_edit_color', methods: ['POST'])]
        public function editColor(Color $color, Request $request, EntityManagerInterface $em): Response 
        {
            $appearanceType = $request->request->get('appearance_type'); // "hex" or "image"
            $color->setName($request->request->get('name'));
            $color->setPrice((float)$request->request->get('price'));
            $color->setAvailability($request->request->has('available'));

            $imageFile = $request->files->get('image');
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/colors/';

            if ($appearanceType === 'hex') {
                // 🎨 If user switched to hex mode
                $color->setColorHex($request->request->get('colorHex'));
                
                // Remove any existing image file
                if ($color->getImagePath()) {
                    $oldPath = $this->getParameter('kernel.project_dir') . '/public/' . $color->getImagePath();
                    if (file_exists($oldPath)) unlink($oldPath);
                    $color->setImagePath(null);
                }
            } 
            elseif ($appearanceType === 'image') {
                // 🖼 User switched to image mode
                $color->setColorHex(null);

                if ($imageFile) {
                    $maxSize = 5 * 1024 * 1024;
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                    if ($imageFile->getSize() > $maxSize) {
                        $this->addFlash('error', 'Image too large (max 5 MB).');
                        return $this->redirectToRoute('admin_colors');
                    }

                    if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) {
                        $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                        return $this->redirectToRoute('admin_colors');
                    }

                    if (!file_exists($uploadsDir)) mkdir($uploadsDir, 0777, true);

                    // Delete old image first
                    if ($color->getImagePath()) {
                        $oldPath = $this->getParameter('kernel.project_dir') . '/public/' . $color->getImagePath();
                        if (file_exists($oldPath)) unlink($oldPath);
                    }

                    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                    $newFilename = sprintf('color_%d_%s_%s.%s', $color->getId(), $safeName, time(), $imageFile->guessExtension());

                    $imageFile->move($uploadsDir, $newFilename);
                    $color->setImagePath('uploads/colors/' . $newFilename);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Color updated successfully!');
            return $this->redirectToRoute('admin_colors');
        }





        # DELETE
        #[Route('/admin/delete-color/{id}', name: 'admin_delete_color', methods: ['POST'])]
        public function deleteColor(Color $color, EntityManagerInterface $em, Request $request): Response
        {
            if ($this->isCsrfTokenValid('delete_color', $request->request->get('_token'))) {
                // If color has an image
                if ($color->getImagePath()) {
                    $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $color->getImagePath();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $em->remove($color);
                $em->flush();

                $this->addFlash('success', 'Color deleted successfully!');
            } else {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            return $this->redirectToRoute('admin_colors');
        }
    #****************************************COLORS****************************************#

    #[Route('/admin/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }
}
