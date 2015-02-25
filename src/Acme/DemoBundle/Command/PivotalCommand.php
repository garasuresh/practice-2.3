<?php
/**
 * Created by JetBrains PhpStorm.
 * User: suresh
 * Date: 24/02/15
 * Time: 15:46
 * To change this template use File | Settings | File Templates.
 */

namespace Acme\DemoBundle\Command;

use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Acme\DemoBundle\Data\Pivotal;

class PivotalCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('pivotal:import')
            ->setDescription(
                'Pivoatal import'
            )
            ->addArgument(
                'projectId',
                null,
                InputOption::VALUE_REQUIRED,
                'Project id'
            )
            ->addOption(
                'projectName',
                null,
                InputOption::VALUE_REQUIRED,
                'Project Name'
            )
            ->addOption(
                'csv',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the csv file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pivotalArray = array();
        $pivotalInfo = array();

        $filePath = $input->getOption('csv');
        $projectName = $input->getOption('projectName');
        $projectId = $input->getArgument('projectId');

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Input CSV file is readable
            $line = 1;
            $header = array();

            // Go through each line in the file and create and ensure a member is created for each record if applicable
            while (($row = fgetcsv($handle)) !== false) {
                if (1 === $line) {
                    // First line store as header record.
                    foreach ($row as $key => $value) {
                        $value = preg_replace("/[^A-Za-z0-9 ]/", '', $value);
                        $value = strtolower($value);
                        $row[$key] = $value;
                    }
                    $header = $row;

                } else {
                    $data = array();
                    foreach ($row as $key => $value) {
                        $value = preg_replace('/\s+/', ' ', $value);
                        $value = trim($value);

                        if ($value == "") {
                            $value = null;
                        }

                        $value = trim($value, ',');
                        if ($value == "") {
                            $value = null;
                        }

                        $data[$header[$key]] = $value;
                    }


                    $comment = $data['comment'];
                    $commentArray = explode(" ", $comment);
                    if(isset($commentArray[1])){
                        $ptId = trim(str_replace("#","",$commentArray[1]));

                        // Pivotal api object
                        $pivotalObj = new Pivotal();
                        $pivotalObj->token = 'b0f50fbfd7975a52483654fc96dfa572';
                        $pivotalObj->project = $projectId;

                        if(is_numeric($ptId)){
                            $storyInfo = $pivotalObj->getStory($ptId);
                            $storyInfo = (array)$storyInfo;
                            $stId = $storyInfo["id"];


                            // working
//                        $pivotalInfoStr = implode(",",$data);
//                        $pivotalInfoStr = $pivotalInfoStr .",". $stId.",".  $storyInfo["estimate"];

                            if(array_key_exists ( $stId , $pivotalArray )){
                                $pivotalInfoStr = $stId.",".$data["name"].",".$data["hours"];
                            }else{
                                $pivotalArray[$stId] = $storyInfo["estimate"];
                                $pivotalInfoStr = $stId.",".$data["name"].",".$data["hours"].",".$storyInfo["estimate"];
                            }

                            array_push($pivotalInfo, $pivotalInfoStr);
                        }
                    }
                }

                $file = fopen(getenv("HOME")."/".$projectName.".csv","w");
                foreach ($pivotalInfo as $line) {
                    fputcsv($file,explode(',',$line));
                }
                fclose($file);

                $line++;
            }

            $output->writeln(print_r($pivotalArray));

            fclose($handle);
        } else {
            $output->writeln("<error>Unable to open source file</error>");
        }
    }

//php app/console pivotal:import 1080796 --projectName=TNE --csv=/home/suresh/Downloads/TNE.csv
}