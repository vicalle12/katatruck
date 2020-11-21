<?php


namespace App\Carnovo\Cars\Infrastructure\Persistence\Repository;


use App\Carnovo\Cars\Domain\Interfaces\CarsRepository;
use App\Carnovo\Cars\Domain\Model\Brand;
use App\Carnovo\Cars\Domain\Model\Car as CarDomain;
use App\Carnovo\Cars\Domain\Model\CarsCollection;
use App\Carnovo\Cars\Domain\Model\Currency;
use App\Carnovo\Cars\Domain\Model\Model;
use App\Carnovo\Cars\Domain\Model\Price;
use App\Carnovo\Cars\Infrastructure\Persistence\Doctrine\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\AssignOp\Mod;

class DoctrineCarsRepository implements CarsRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findCarsBy(?Brand $brand = null, ?Model $model = null, ?Price $lessEqual = null, ?Price $moreEqual = null): CarsCollection
        {
            $query = $this->entityManager->createQueryBuilder()
                ->select('c')
                ->from(Car::class, "c")
            ;

            if (!is_null($brand)) {
                $query->andWhere("c.brand = :brand")
                    ->setParameter("brand", $brand->getValue());
            }

            if (!is_null($model)) {
                $query->andWhere("c.model = :model")
                    ->setParameter("model", $model->getValue());
            }

            if (!is_null($lessEqual)) {
                $query->andWhere("c.price_amount <= :less")
                    ->setParameter("lest", $lessEqual->getAmount());
            }

            if (!is_null($moreEqual)) {
                $query->andWhere("c.price_amount >= :more")
                    ->setParameter("more", $moreEqual->getAmount());
            }

            $result = $query
                ->getQuery()
                ->getResult();

            return new CarsCollection(array_map(function (Car $car) {
                return new CarDomain(
                    new Brand($car->getBrand()),
                    new Model($car->getModel()),
                    new Price($car->getPriceAmount(), new Currency($car->getPriceCurrency()))
                );
            }, $result));
        }
}