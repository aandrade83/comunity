import React{ useState,useEffect, useCallback, useMemo } from 'react';
import {
  View,
  Text,
  TextInput,
  StyleSheet,
  ScrollView,
  Button,
} from 'react-native';
import { MOCK_COINS } from './mockData';

/** * CHALLENGE:
 * 1. Type the component properties and data.
 * 2. Implement filtering by currency name (search). Debounce the search input by 500ms
 * 3. The ‘CoinItem’ component must be cached correctly, Do not render the list when the button is pressed.
 * 4. Optimize list rendering
 * 5. Calculate and display total price by Coin
 * 6. Bonus: Implement a currency format (USD) and dynamic color for ‘change’.
 */

const CoinItem = ({ item }) => {
  console.log(`Renderizando: ${item.name}`);
  return (
    <View style={styles.item}>
      <Text style={styles.name}>{item.name}</Text>
      <Text style={styles.price}>{item.price.toFixed(2)}</Text>
    </View>
  );
};

export default function CryptoMarket() {
  const [search, setSearch] = useState('');
  const [text, setText] = useState('');
  const [coins, setCoins] = useState(MOCK_COINS);

  return (
    <View style={styles.container}>
      <Button
        title="Change text"
        onPress={() => {
          console.log('Button Pressed');
          setText('Button Pressed');
        }}>
        Press
      </Button>

      <TextInput
        style={styles.searchBar}
        placeholder="Buscar moneda..."
        value={search}
        onChangeText={setSearch}
      />

      <ScrollView>
        {coins.map((coin) => (
          <CoinItem item={coin} />
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, paddingTop: 60, backgroundColor: '#f5f5f5' },
  searchBar: {
    height: 50,
    backgroundColor: '#fff',
    margin: 10,
    paddingHorizontal: 15,
    borderRadius: 10,
    paddingVertical: 10,
  },
  item: {
    padding: 20,
    borderBottomWidth: 1,
    borderColor: '#eee',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  name: { fontSize: 18, fontWeight: 'bold' },
  price: { fontSize: 16, color: '#666' },
});
