<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use App\Traits\SoftDeleteableEntity;
use App\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * BackupStatus
 *
 * @ORM\Table(name="backup_status")
 * @ORM\Entity(repositoryClass="App\Repository\BackupStatusRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"backup_status_read", "read"}},
 *     "denormalization_context"={"groups"={"backup_status_write"}},
 *     "order"={"id": "DESC"}
 * },
 *     collectionOperations={
 *          "get"={
 *              "access_control"="is_granted('ROLE_BACKUP_STATUS_LIST')"
 *          },
 *          "post"={
 *              "access_control"="is_granted('ROLE_BACKUP_STATUS_CREATE')"
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "access_control"="is_granted('ROLE_BACKUP_STATUS_SHOW')"
 *          },
 *          "put"={
 *              "access_control"="is_granted('ROLE_BACKUP_STATUS_UPDATE')"
 *          },
 *          "delete"={
 *              "access_control"="is_granted('ROLE_BACKUP_STATUS_DELETE')"
 *          }
 *     })
 * @ApiFilter(DateFilter::class, properties={"createdAt", "updatedAt"})
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "name": "ipartial",
 * })
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *          "id",
 *          "name",
 *          "createdAt",
 *          "updatedAt"
 *     }
 * )
 */
class BackupStatus
{
    public const STATUS_PENDING = 1;
    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_READY = 3;
    public const STATUS_FAILED = 4;

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;

    /**
     * Hook blameable behavior
     * updates createdBy, updatedBy fields
     */
    use BlameableEntity;

    /**
     * Hook SoftDeleteable behavior
     * updates deletedAt field
     */
    use SoftDeleteableEntity;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "backup_status_read",
     *     "backup_read"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Gedmo\Versioned
     * @Groups({
     *     "backup_status_read",
     *     "backup_status_write",
     *     "backup_read"
     * })
     * @Assert\NotBlank()
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
