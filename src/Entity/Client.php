<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Interfaces\ClientInterface;
use App\Interfaces\SearchInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use App\Traits\SoftDeleteableEntity;
use App\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * Client
 *
 * @ORM\Table(name="client")
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable
 * @ApiResource(
 *     attributes={
 *          "normalization_context"={"groups"={"client_read", "read"}},
 *          "denormalization_context"={"groups"={"client_write"}},
 *          "order"={"id": "DESC"}
 *     },
 *     collectionOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={"client_read_collection", "read"}
 *              },
 *              "access_control"="is_granted('ROLE_CLIENT_LIST')"
 *          },
 *          "post"={
 *              "access_control"="is_granted('ROLE_CLIENT_CREATE')"
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "access_control"="is_granted('ROLE_CLIENT_SHOW')"
 *          },
 *          "put"={
 *              "access_control"="is_granted('ROLE_CLIENT_UPDATE')"
 *          },
 *          "delete"={
 *              "access_control"="is_granted('ROLE_CLIENT_DELETE')"
 *          }
 *     }
 * )
 * @ApiFilter(DateFilter::class, properties={"createdAt", "updatedAt"})
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "name": "ipartial",
 *     "contacts.value": "ipartial",
 *     "description": "ipartial"
 * })
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *          "id",
 *          "name",
 *          "description",
 *          "createdAt",
 *          "updatedAt"
 *     }
 * )
 */
class Client implements ClientInterface, SearchInterface
{
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
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "client_read",
     *     "client_read_collection",
     *     "company_read",
     *     "company_write",
     *     "document_read",
     *     "project_read",
     *     "document_write",
     *     "project_write",
     *     "task_read",
     *     "contact_read",
     *     "contact_write",
     *     "order_header_read",
     *     "order_header_read_collection",
     *     "order_header_write",
     *     "address_read",
     *     "address_write"
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Gedmo\Versioned
     * @Groups({
     *     "client_read",
     *     "client_read_collection",
     *     "client_write",
     *     "company_read",
     *     "company_write",
     *     "document_read",
     *     "project_read",
     *     "document_write",
     *     "task_read",
     *     "contact_read",
     *     "order_header_read",
     *     "order_header_read_collection"
     * })
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Address", inversedBy="clients")
     * @ORM\OrderBy({"id" = "DESC"})
     * @Groups({
     *     "client_read",
     *     "client_write"
     * })
     * @ApiSubresource()
     * @Assert\Valid()
     */
    private $addresses;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Versioned
     * @Groups({
     *     "client_read",
     *     "client_read_collection",
     *     "client_write",
     *     "company_read"
     * })
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Contact", inversedBy="clients", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Groups({
     *     "client_read",
     *     "client_read_collection",
     *     "client_write"
     * })
     * @ApiSubresource()
     * @Assert\Valid()
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="client", cascade={"persist"}, orphanRemoval=true)
     * @Groups({
     *     "document_read",
     *     "client_read",
     *     "client_write"
     * })
     * @ORM\OrderBy({"id" = "ASC"})
     * @ApiSubresource()
     * @Assert\Valid()
     */
    private $projects;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", mappedBy="clients")
     * @Groups({
     *     "client_read",
     *     "client_write"
     * })
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $companies;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Document", mappedBy="clients", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     * @ApiSubresource()
     */
    private $documents;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Client")
     * @Groups({
     *     "client_read",
     *     "client_write"
     * })
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $clients;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this;
    }

    /**
     * Search text
     *
     * @return string
     */
    public function getSearchText(): string
    {
        return implode(
            ' ',
            [
                $this->getName(),
                $this->getDescription(),
            ]
        );
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
        }

        return $this;
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
        }

        return $this;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setClient($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getClient() === $this) {
                $project->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->addClient($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            $company->removeClient($this);
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->addClient($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            $document->removeClient($this);
        }

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
        }

        return $this;
    }
}
