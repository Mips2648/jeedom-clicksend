
class Texte(object):

    __textes = dict()
    __priorite = ["precedent", "core"]

    # --- Les méthodes de static ---

    @classmethod
    def by_texte (cls, txt, create=True):
        if txt in cls.__textes:
            return cls.__textes[txt]
        if create:
            return cls(txt)
        return None

    @classmethod
    def set_priorite(cls, p):
        result = []
        for prio in p.split(",") + cls.__priorite:
            if not prio in result:
                result.append(prio)
        cls.__priorite = result

    # --- Les méthodes d'instance ---

    def __new__ (cls,txt=""):
        if txt == "":
            return None
        return super().__new__(cls)

    def __init__ (self,txt):
        self.__textes[txt] = self
        self.__texte = txt
        self.__traduction = dict()

    def __del__ (self):
        del self.__textes[self.__texte]

    def add_traduction (self, langue, traduction, source):
        self.__traduction.setdefault(langue,dict())
        self.__traduction[langue].setdefault(source,list())
        if not traduction in self.__traduction[langue][source]:
            self.__traduction[langue][source].append(traduction)

    def select_traduction(self, source, langue):
        choix = self.__traduction[langue][source]
        if len(choix) == 1:
            return choix[0]
        print (f'\nLa source <{source}> propose plusieurs traductions en <{langue}> pour : "{self.__texte}"\n')
        for i in range (len(choix)):
            print (i+1, ":", choix[i])
        print ()
        while True:
            try:
                reponse = int(input ("Laquelle de ces traductions doit être utilisée (#) ? "))
            except ValueError:
                print ("Prière de saisir le numéro de la traduction voulue")
                reponse = 0
            reponse = reponse -1
            if reponse >= 0 and reponse < len(choix):
                return (choix[reponse])

    def get_traduction (self, langue):
        traduction = self.__texte
        if langue == "fr_FR":
            # On ne traduit pas le Français en Français
            return (self.__texte, traduction)

        if not langue in self.__traduction:
            # Il n'y a pas de traduction disponible pour cette langue
            return (self.__texte, traduction)

        OK = False
        for source in self.__priorite:
            if not OK and source in self.__traduction[langue]:
                if source == "precedent":
                    # On conserve la version pécédente uniquement si elle
                    # a été traduite
                    if self.__traduction[langue][source] != self.__texte:
                        traduction = self.__traduction[langue][source]
                        OK = True
                # elif source == "core":
                #     traduction = self.select_traduction(source, langue)
                #     OK = True
        return (self.__texte, traduction)


    def get_texte (self):
        return self.__texte
